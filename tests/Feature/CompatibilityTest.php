<?php

use App\Logic\GeoIpLocator;
use App\Models\Installation;
use GeoIp2\Exception\AddressNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Mockery\MockInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

uses(RefreshDatabase::class);

test('cannot send empty request')
    ->postJson('/')
    ->assertUnprocessable()
    ->assertInvalid(['uuid', 'release', 'type']);

test('cannot insert invalid type')
    ->postJson('/', ['type' => 'hello'])
    ->assertUnprocessable()
    ->assertInvalid(['type' => 'in']);

test('cannot insert invalid version', function (string $release) {
    $this->postJson('/', ['release' => $release])
        ->assertUnprocessable()
        ->assertInvalid(['release' => 'format']);
})->with([
    'hello',
    'x.y.z',
    'x.y',
    'x.',
    '99.99.99x'
]);

it('can handle post data', function () {
    $this->mock(GeoIpLocator::class, function (MockInterface $mock) {
        $mock->shouldReceive('locate')
            ->once()
            ->andReturn('IT');
    });

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
})->skip(fn () => !Route::has('installation.index'));

it('can handle if ip is not found', function () {
    /** @var Tests\TestCase $this */
    $this->mock(GeoIpLocator::class, function (MockInterface $mock) {
        $mock->shouldReceive('locate')
            ->with('127.0.0.1')
            ->once()
            ->andThrow(new AddressNotFoundException());
    });

    $installation = Installation::factory()->make();
    $this->withoutExceptionHandling()->postJson(
        '/',
        [
            'method' => 'add_info',
            'uuid' => $installation->uuid,
            'release' => $installation->release,
            'type' => $installation->type
        ]
    );
})->throws(UnprocessableEntityHttpException::class);
