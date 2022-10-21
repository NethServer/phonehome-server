<?php

namespace App\Console\Commands;

use App\Http\Requests\StoreInstallationRequest;
use App\Models\Country;
use App\Models\Installation;
use App\Models\Version;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;

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
     *
     * @return int
     */
    public function handle(): int
    {
        $this->table(['config', 'value'], [
            ['host', config('database.connections.migration.host')],
            ['port', config('database.connections.migration.port')],
            ['database', config('database.connections.migration.database')],
            ['username', config('database.connections.migration.username')],
            ['password', config('database.connections.migration.password')],
        ]);
        if (!$this->confirm('Do you confirm this configuration for the data migration? The application will be put in maintainance mode.')) {
            return self::SUCCESS;
        }

        if (!Schema::connection('migration')->hasTable('phone_home_tb')) {
            $this->error('Cannot find `phone_home_tb` table.');
            return self::FAILURE;
        }

        $this->info('Migrating data inside a transaction, please wait...');
        $this->call('down');
        DB::transaction(function () {
            $this->info('Migrating countries...');
            $this->importCountries();
            $this->newLine();
            $this->info('Countries migrated.');

            $this->info('Migrating releases...');
            $this->importReleases();
            $this->newLine();
            $this->info('Releases migrated.');

            $bar = $this->output->createProgressBar(DB::connection('migration')->table('phone_home_tb')->count());
            $bar->start();
            $validator = Validator::make(
                [],
                array_merge(
                    StoreInstallationRequest::$rules,
                    [
                        'country_code' => 'required|max:2',
                        'country_name' => 'required'
                    ]
                )
            );
            DB::connection('migration')->table('phone_home_tb')->orderBy('uuid')
                ->lazy()->each(
                    function ($row) use ($bar, $validator) {
                        $validator = $validator->setData(
                            [
                                'uuid' => $row->uuid,
                                'release' => $row->release_tag,
                                'type' => $row->type,
                                'country_code' => $row->country_code,
                                'country_name' => $row->country_name
                            ]
                        );
                        if ($validator->passes()) {
                            $installation = Installation::firstOrNew([
                                'uuid' => $row->uuid
                            ], [
                                'type' => $row->type
                            ]);
                            $installation->created_at = $row->reg_date;
                            $installation->updated_at = $row->reg_date;

                            $country = Country::where('code', $row->country_code)->firstOrFail();
                            $installation->country()->associate($country);
                            $country->save();

                            $version = Version::where('tag', $row->release_tag)->firstOrFail();
                            $installation->version()->associate($version);
                            $country->save();
                            $installation->save();
                        } else {
                            $this->printValidationErrors($validator->errors());
                        }
                        $bar->advance();
                    }
                );
            $bar->finish();
        });

        $this->newLine();
        $this->call('up');
        $this->info('Migration successful, you may now remove additional environment variables starting with "MIGRATION_*"');

        return self::SUCCESS;
    }

    private function importCountries(): void
    {
        // query executed
        $countries = DB::connection('migration')
            ->table('phone_home_tb')->select('country_name', 'country_code')
            ->groupBy('country_name', 'country_code')->orderBy('country_code')->get();
        // validator instance to run data against
        $validator = Validator::make([], [
            'country_code' => 'required|max:2',
            'country_name' => 'required'
        ]);
        // iterate through all data
        $this->withProgressBar($countries, function ($row) use ($validator) {
            // set data in validator instance
            $validator->setData([
                'country_code' => $row->country_code,
                'country_name' => $row->country_name
            ]);
            if ($validator->passes()) {
                Country::firstOrCreate([
                    'code' => $row->country_code
                ], [
                    'name' => $row->country_name
                ]);
            } else {
                $this->printValidationErrors($validator->errors());
            }
        });
    }

    private function importReleases(): void
    {
        // query executed
        $releases = DB::connection('migration')
            ->table('phone_home_tb')->select('release_tag')
            ->groupBy('release_tag')->orderBy('release_tag')->get();
        // validator instance to run data against
        $validator = Validator::make([], [
            'release' => [
                'required',
                'regex:/^\d+\.\d+\.?\d*$/m' // uses preg_match
            ]
        ]);
        // iterate through all data
        $this->withProgressBar($releases, function ($row) use ($validator) {
            // set data in validator instance
            $validator->setData([
                'release' => $row->release_tag
            ]);
            if ($validator->passes()) {
                Version::firstOrCreate([
                    'tag' => $row->release_tag
                ]);
            } else {
                $this->printValidationErrors($validator->errors());
            }
        });
    }


    private function printValidationErrors(MessageBag $errors): void
    {
        $this->newLine();
        $this->warn('Validation failed with: ');
        $this->warn($errors);
    }
}
