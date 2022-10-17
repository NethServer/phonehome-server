<?php

use App\Logic\GeoIpLocator;
use App\Models\Installation;
use GeoIp2\Exception\AddressNotFoundException;
use GeoIp2\Record\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\Fluent\AssertableJson;
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

test('cannot insert invalid version', function (string $tag) {
    $this->postJson('/', ['release' => $tag])
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
    $country = $this->mock(Country::class);
    $country->name = 'Italy';
    $country->isoCode = 'IT';

    $this->mock(GeoIpLocator::class, function (MockInterface $mock) use ($country) {
        $mock->shouldReceive('locate')
            ->once()
            ->andReturn($country);
    });

    $installation = Installation::factory()->make();
    /** @var Tests\TestCase $this */
    $this->postJson(
        '/',
        [
            'method' => 'add_info',
            'uuid' => $installation->uuid,
            'release' => $installation->version->tag,
            'type' => $installation->type
        ]
    )->assertStatus(200);
});

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
            'release' => $installation->version->tag,
            'type' => $installation->type
        ]
    );
})->throws(UnprocessableEntityHttpException::class);
