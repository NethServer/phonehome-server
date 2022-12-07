<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->group('installation');

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
    'nextsecurity',
]);

it('can\'t accept invalid uuid', function (string $schema, string $installation, string $uuid) {
    $request = [
        '$schema' => $schema,
        'uuid' => $uuid,
        'installation' => $installation,
        'facts' => [
            'cluster' => (object) [],
            'nodes' => [
                '1' => [
                    'distro' => [
                        'name' => 'rocky',
                        'version' => '9.1',
                    ],
                    'version' => '8.0.0',
                ],
            ],
            'modules' => (object) [],
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
    'nextsecurity',
])->with([
    '',
    123,
    'hboygapeyfpavwepagyweypgadpcawyvedapwgvepiyagwdpègyawpve]',
]);

it('can\'t accept invalid nethserver version', function (string $schema, string $version) {
    $request = [
        '$schema' => $schema,
        'uuid' => fake()->uuid(),
        'installation' => 'nethserver',
        'facts' => [
            'cluster' => (object) [],
            'nodes' => [
                '1' => [
                    'distro' => [
                        'name' => 'rocky',
                        'version' => '9.1',
                    ],
                    'version' => $version,
                ],
            ],
            'modules' => (object) [],
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

it('can\'t accept invalid nextsecurity version', function (string $schema, string $version) {
    $request = [
        '$schema' => $schema,
        'uuid' => fake()->uuid(),
        'installation' => 'nextsecurity',
        'facts' => [
            'distro' => [
                'name' => 'rocky',
                'version' => '9.1',
            ],
            'version' => $version,
        ],
    ];
    $this->postJson('/api/installation', $request)
        ->assertUnprocessable()
        ->assertInvalid([
            '/facts/version' => 'pattern',
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
