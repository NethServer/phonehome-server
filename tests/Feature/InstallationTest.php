<?php

use App\Logic\GeoIpLocator;
use App\Models\Installation;
use GeoIp2\Exception\AddressNotFoundException;
use GeoIp2\Record\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery\MockInterface;

uses(RefreshDatabase::class)
    ->group('installation')
    ->beforeEach(function () {
        $this->mock(GeoIpLocator::class);
    });

const SCHEMA_2022_12 = 'https://schema.nethserver.org/facts/2022-12.json';

it('can\'t push without $schema')
    ->postJson('/api/installation')
    ->assertUnprocessable()
    ->assertInvalid('$schema');

it('can\'t process outsourced schemas', function (string $schema) {
    $this->postJson('/api/installation', [
        '$schema' => $schema,
    ])
        ->assertUnprocessable()
        ->assertInvalid('$schema');
})->with([
    'http://schema.nethserver.org/hello',
    'ftp://schema.nethserver.org/hello',
    'https://schema.org/hello',
    'http://schema.nethserver.or/hello',
]);

it('can\'t accept missing UUID', function (string $schema) {
    $this->postJson('/api/installation', [
        '$schema' => $schema,
    ])
        ->assertUnprocessable()
        ->assertInvalid([
            '/' => 'uuid',
        ]);
})->with([
    SCHEMA_2022_12,
]);

it('can\'t accept missing installation', function (string $schema) {
    $this->postJson('/api/installation', [
        '$schema' => $schema,
        'uuid' => fake()->uuid(),
    ])
        ->assertUnprocessable()
        ->assertInvalid([
            '/' => 'installation',
        ]);
})->with([
    SCHEMA_2022_12,
]);

it('can\'t accept missing facts', function (string $schema, string $installation) {
    $this->postJson('/api/installation', [
        '$schema' => $schema,
        'uuid' => fake()->uuid(),
        'installation' => $installation,
    ])
        ->assertUnprocessable()
        ->assertInvalid([
            '/' => 'facts',
        ]);
})->with([
    SCHEMA_2022_12,
])->with([
    'nethserver',
    'nethsecurity',
]);

it('can\'t accept invalid uuid', function (string $schema, string $installation, string $uuid) {
    $request = [
        '$schema' => $schema,
        'uuid' => $uuid,
        'installation' => $installation,
        'facts' => [
            'cluster' => [],
            'nodes' => [
                '1' => [
                    'distro' => [
                        'name' => 'rocky',
                        'version' => '9.1',
                    ],
                    'version' => '8.0.0',
                ],
            ],
            'modules' => [],
        ],
    ];
    $this->postJson('/api/installation', $request)
        ->assertUnprocessable()
        ->assertInvalid([
            '/uuid' => 'format',
        ]);
})->with([
    SCHEMA_2022_12,
])->with([
    'nethserver',
    'nethsecurity',
])->with([
    '',
    '123',
    'hboygapeyfpavwepagyweypgadpcawyvedapwgvepiyagwdpègyawpve]',
]);

it('can\'t accept invalid nethserver version', function (string $schema, string $version) {
    $request = [
        '$schema' => $schema,
        'uuid' => fake()->uuid(),
        'installation' => 'nethserver',
        'facts' => [
            'cluster' => [],
            'nodes' => [
                '1' => [
                    'distro' => [
                        'name' => 'rocky',
                        'version' => '9.1',
                    ],
                    'version' => $version,
                ],
            ],
            'modules' => [],
        ],
    ];
    $this->postJson('/api/installation', $request)
        ->assertUnprocessable()
        ->assertInvalid([
            '/facts/nodes/1/version' => 'pattern',
        ]);
})->with([
    SCHEMA_2022_12,
])->with([
    '8.0.a',
    'X.Y',
    'X',
    '8',
    '9.a.6',
]);

it('saves correctly new nethserver installation', function (string $schema) {
    $installation = Installation::factory()->nethserver()->make();
    $request = array_merge($installation->data, [
        '$schema' => $schema,
    ]);
    $this->mock(GeoIpLocator::class, function (MockInterface $mock) {
        $country = new Country([
            'iso_code' => 'IT',
            'names' => ['en' => 'Italy'],
        ]);
        $mock->shouldReceive('locate')
            ->once()
            ->andReturn($country);
    });
    $this->postJson('/api/installation', $request)
        ->assertCreated()
        ->assertJson([]);
    $this->assertDatabaseCount('installations', 1);
    $this->assertDatabaseHas('installations', [
        'data->uuid' => $request['uuid'],
        'data->installation' => $request['installation'],
        'data->facts' => json_encode($request['facts']),
    ]);
})->with([
    SCHEMA_2022_12,
]);

it('saves correctly new nethsecurity installation', function (string $schema) {
    $installation = Installation::factory()->nethsecurity()->make();
    $request = array_merge($installation->data, [
        '$schema' => $schema,
    ]);

    $this->mock(GeoIpLocator::class, function (MockInterface $mock) {
        $country = new Country([
            'iso_code' => 'IT',
            'names' => ['en' => 'Italy'],
        ]);
        $mock->shouldReceive('locate')
            ->once()
            ->andReturn($country);
    });

    $this->postJson('/api/installation', $request)
        ->assertCreated()
        ->assertJson([]);

    $this->assertDatabaseCount('installations', 1);
    $this->assertDatabaseHas('installations', [
        'data->uuid' => $request['uuid'],
        'data->installation' => $request['installation'],
        'data->facts' => json_encode($request['facts']),
    ]);
})->with([
    SCHEMA_2022_12,
]);

it('updates installation', function (string $schema, string $type) {
    $installation = Installation::factory()->$type()->create();
    $newInstallation = Installation::factory()->$type()->make();

    $request = array_merge($newInstallation->data, [
        '$schema' => $schema,
    ]);
    $request['uuid'] = $installation->data['uuid'];

    $this->mock(GeoIpLocator::class, function (MockInterface $mock) {
        $country = new Country([
            'iso_code' => 'IT',
            'names' => ['en' => 'Italy'],
        ]);
        $mock->shouldReceive('locate')
            ->once()
            ->andReturn($country);
    });

    $this->postJson('/api/installation', $request)
        ->assertCreated()
        ->assertJson([]);

    $this->assertDatabaseCount('installations', 1);
    $this->assertDatabaseHas('installations', [
        'id' => $installation->id,
        'data->uuid' => $request['uuid'],
        'data->installation' => $request['installation'],
        'data->facts' => json_encode($request['facts']),
    ]);
})->with([
    SCHEMA_2022_12,
])->with([
    'nethserver',
    'nethsecurity',
]);

it('fails to resolve location', function (string $schema, string $type) {
    $installation = Installation::factory()->$type()->make();

    $request = array_merge($installation->data, [
        '$schema' => $schema,
    ]);

    $this->mock(GeoIpLocator::class, function (MockInterface $mock) {
        $mock->shouldReceive('locate')
            ->once()
            ->andThrow(new AddressNotFoundException());
    });

    $this->postJson('/api/installation', $request)
        ->assertUnprocessable();

    Log::shouldReceive('error')
        ->with('Couldn\'t resolve location for: 127.0.0.1 ('.$request['uuid'].')');

    $this->assertDatabaseCount('installations', 0);
})->with([
    SCHEMA_2022_12,
])->with([
    'nethserver',
    'nethsecurity',
]);
