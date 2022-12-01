<?php

use App\Exceptions\NotImplemented;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can\'t push without mandatory data values')
    ->postJson('/api/installation')
    ->assertUnprocessable()
    ->assertInvalid('uuid', 'versions');

it('can\'t accept missing or invalid UUID', function (string $uuid) {
    $this->postJson('/api/installation', [
        'uuid' => $uuid,
    ])
    ->assertUnprocessable()
    ->assertInvalid('uuid');
})->with([
    '',
    123,
    'hboygapeyfpavwepagyweypgadpcawyvedapwgvepiyagwdpègyawpve]',
]);

it('can\'t accept invalid version', function (string $version) {
    $this->postJson('/api/installation', [
        'version' => $version,
    ])
    ->assertUnprocessable()
    ->assertInvalid('version');
})->with([
    '8.0.a',
    'X.Y',
    'X',
    '8',
    '9.a.6',
]);

it('can save additional data to database', function () {
    $version = '8.0.0';
    $uuid = fake()->uuid();
    // podman --version | cut -d ' ' -f 3
    $podman = '3.4.4';
    // rpm -qa
    $installed_packages = 'nftables-0.9.8-12.el9.x86_64
        python3-nftables-0.9.8-12.el9.x86_64
        python3-firewall-1.0.0-4.el9.noarch
        openssl-libs-3.0.1-43.el9_0.x86_64
        aardvark-dns-1.0.1-36.el9_0.x86_64
        netavark-1.0.1-36.el9_0.x86_64
        oniguruma-6.9.6-1.el9.5.x86_64
        libnet-1.2-6.el9.x86_64
        criu-3.15-13.el9.x86_64';

    $this->withoutExceptionHandling()
        ->postJson('/api/installation', [
            'version' => $version,
            'uuid' => $uuid,
            'podman' => $podman,
            'installed_packages' => $installed_packages,
        ])
    ->assertCreated();
    $this->assertDatabaseCount('installations', 1);
    $this->assertDatabaseHas('installations', [
        'version' => $version,
        'uuid' => $uuid,
        'data' => json_encode([
            'podman' => $podman,
            'installed_packages' => $installed_packages,
        ]),
    ]);
})->throws(NotImplemented::class);
