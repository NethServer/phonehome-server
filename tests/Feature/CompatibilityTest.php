<?php

use App\Models\Installation;

test('cannot send empty request')
    ->postJson('/')
    ->assertUnprocessable()
    ->assertInvalid(['uuid', 'release', 'type']);

test('cannot insert invalid type')
    ->postJson('/', ['type' => 'hello'])
    ->assertUnprocessable()
    ->assertInvalid(['type' => 'in']);

test('cannot insert invalid version', function () {
    $this->postJson('/', ['release' => 'hello'])
        ->assertUnprocessable()
        ->assertInvalid(['release' => 'regex']);
})->skip();

it('can handle post data', function () {
    $installation = Installation::factory()->make();
    /** @var Tests\TestCase $this */
    $this->postJson(
        '/',
        [
            'method' => 'add_info',
            'uuid' => $installation->uuid,
            'release' => $installation->release,
            'type' => $installation->type
        ]
    )->assertStatus(200);
    $this->get('/api/installations')
        ->assertStatus(200);
})->skip();