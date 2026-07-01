<?php

use App\Jobs\RefreshPhonehomeDashboardMaterializedViews;
use App\Models\Installation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class)->group('phonehome-dashboard');

it('flattens active installations into phonehome_installations, classified by category', function () {
    $nethsecurity = Installation::factory()->nethsecurity()->create();
    $nethserver8 = Installation::factory()->nethserver()->create();
    $legacy = Installation::factory()->create();

    RefreshPhonehomeDashboardMaterializedViews::dispatchSync();

    $rows = DB::table('phonehome_installations')->get()->keyBy('installation_id');

    expect($rows[$nethsecurity->id]->category)->toBe('nethsecurity')
        ->and($rows[$nethsecurity->id]->version)->toBe($nethsecurity->data['facts']['distro']['version'])
        ->and($rows[$nethsecurity->id]->node_count)->toBeNull();

    expect($rows[$nethserver8->id]->category)->toBe('nethserver8')
        ->and((int) $rows[$nethserver8->id]->node_count)->toBe(1)
        ->and($rows[$nethserver8->id]->version)->toBeNull();

    expect($rows[$legacy->id]->category)->toBe('nethserver67')
        ->and($rows[$legacy->id]->version)->toBe($legacy->data['facts']['version']);
});

it('unnests nethserver8 nodes into phonehome_nethserver8_node_versions', function () {
    $installation = Installation::factory()->nethserver()->create();

    RefreshPhonehomeDashboardMaterializedViews::dispatchSync();

    $rows = DB::table('phonehome_nethserver8_node_versions')
        ->where('installation_id', $installation->id)
        ->get();

    expect($rows)->toHaveCount(1)
        ->and($rows->first()->node_key)->toBe('1')
        ->and($rows->first()->version)->toBe($installation->data['facts']['nodes']['1']['version']);
});

it('refreshes phonehome_daily_active_counts with historical per-day activity', function () {
    $yesterday = now()->subDay();

    Installation::factory()->nethsecurity()->create([
        'created_at' => $yesterday->copy()->subDays(2),
        'updated_at' => $yesterday,
    ]);

    RefreshPhonehomeDashboardMaterializedViews::dispatchSync();

    $row = DB::table('phonehome_daily_active_counts')
        ->where('day', $yesterday->toDateString())
        ->where('category', 'nethsecurity')
        ->first();

    expect($row)->not->toBeNull()
        ->and((int) $row->active_count)->toBeGreaterThanOrEqual(1);
});
