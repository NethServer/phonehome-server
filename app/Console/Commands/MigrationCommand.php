<?php

//
// Copyright (C) 2022 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\Installation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;

/**
 * @codeCoverageIgnore
 */
class MigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:phonehome:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Allows the migration from older version of Phonehome';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->ask(
            'Please provide the relative path to the CSV file (currently in "'.getcwd().'" directory)',
            'phone_home_tb.csv'
        );
        if (! file_exists($filePath)) {
            $this->error('File '.$filePath.' cannot be found.');

            return self::FAILURE;
        }

        $reader = Reader::createFromPath($filePath);
        $reader->setHeaderOffset(0);
        $totalRows = $reader->count();

        $this->info('In total, '.$totalRows.' records will be imported, the first 3 are:');
        $this->info(collect($reader->fetchOne(0)));
        $this->info(collect($reader->fetchOne(1)));
        $this->info(collect($reader->fetchOne(2)));
        $this->info('While the last one is:');
        $this->info(collect($reader->fetchOne($totalRows - 1)));

        if (! $this->confirm('Do you want to proceed? The application will be put in maintainance mode during the migration.')) {
            return self::SUCCESS;
        }

        $this->info('Migrating data inside a transaction, please wait...');
        $this->call('down');

        DB::transaction(function () use ($reader, $totalRows) {
            $bar = $this->output->createProgressBar($totalRows);
            $bar->start();

            $validator = Validator::make(
                [],
                [
                    'uuid' => 'required|uuid',
                    'release_tag' => [
                        'required',
                        'regex:/^\d+\.\d+\.?\d*$/m', // uses preg_match
                    ],
                    'country_code' => 'required|string|max:2',
                    'country_name' => 'required|string',
                    'reg_date' => 'required|date',
                    'type' => 'required|in:community,enterprise,subscription,NULL',
                ]
            );

            $migrationLogger = Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/migration.log'),
            ]);
            $migrationLogger->notice('Migration Started.');

            $validationFailureCount = 0;
            foreach ($reader->getRecords() as $record) {
                $validator->setData($record);
                if ($validator->passes()) {
                    $installation = Installation::firstOrNew([
                        'data->uuid' => $record['uuid'],
                    ]);
                    $installationData = $installation->data;
                    $installationData['installation'] = 'nethserver';
                    $installationData['facts']['type'] = $record['type'] == 'NULL' ? null : $record['type'];
                    $installationData['facts']['version'] = $record['release_tag'];

                    $installation->created_at = $record['reg_date'];
                    $installation->updated_at = $record['reg_date'];

                    $installation->country()->associate(
                        Country::firstOrCreate([
                            'code' => $record['country_code'],
                        ], [
                            'name' => $record['country_name'],
                        ])
                    );

                    $installation->data = $installationData;
                    $installation->save();
                } else {
                    $validationFailureCount++;
                    $migrationLogger->warning('Validation failed: '.$validator->errors(), $record);
                }
                $bar->advance();
            }
            $bar->finish();
            $this->newLine(2);
            if ($validationFailureCount > 0) {
                $this->warn($validationFailureCount.' records failed the validation process, please check '.storage_path('logs/migration.log').' for more info.');
            }
            $migrationLogger->notice('Migration Finished.');
        });

        $this->info('Migration successful, you may now remove the CSV file.');
        $this->call('up');

        return self::SUCCESS;
    }
}
