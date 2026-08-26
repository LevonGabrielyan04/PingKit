<?php

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    config(['app.admin_email' => 'admin@example.com']);
});

test('guests cannot visit the pulse dashboard', function () {
    $this->get('/pulse')->assertForbidden();
});

test('non-admin users cannot visit the pulse dashboard', function () {
    $user = User::factory()->create(['email' => 'user@example.com']);

    $this->actingAs($user)
        ->get('/pulse')
        ->assertForbidden();
});

test('the admin user can visit the pulse dashboard', function () {
    $user = User::factory()->create(['email' => 'admin@example.com']);

    $this->actingAs($user)
        ->get('/pulse')
        ->assertOk();
});

test('viewPulse gate allows the admin email', function () {
    $user = User::factory()->create(['email' => 'admin@example.com']);

    expect(Gate::forUser($user)->check('viewPulse'))->toBeTrue();
});

test('viewPulse gate denies non-admin users and guests', function () {
    $user = User::factory()->create(['email' => 'user@example.com']);

    expect(Gate::forUser($user)->check('viewPulse'))->toBeFalse()
        ->and(Gate::check('viewPulse'))->toBeFalse();
});

test('pulse ingest uses the dedicated redis connection', function () {
    expect(config('pulse.ingest.driver'))->toBe('redis')
        ->and(config('pulse.ingest.redis.connection'))->toBe('pulse')
        ->and(config('database.redis.pulse.database'))->toBe(env('REDIS_PULSE_DB', '2'));
});

test('pulse dashboard cache can round-trip collection payloads', function () {
    $payload = [
        collect([
            (object) [
                'name' => 'web-1',
                'cpu' => collect([1, 2, 3]),
                'updated_at' => CarbonImmutable::parse('2026-08-26 12:00:00'),
            ],
        ]),
        1.25,
        '2026-08-26 12:00:00',
    ];

    $cache = Cache::store('file');
    $cache->put('laravel:pulse:servers-round-trip', $payload, 60);

    $retrieved = $cache->get('laravel:pulse:servers-round-trip');

    expect($retrieved[0])->toBeInstanceOf(Collection::class)
        ->and($retrieved[0]->first())->toBeInstanceOf(stdClass::class)
        ->and($retrieved[0]->first()->cpu)->toBeInstanceOf(Collection::class)
        ->and($retrieved[0]->first()->updated_at)->toBeInstanceOf(CarbonImmutable::class)
        ->and($retrieved[0]->first()->name)->toBe('web-1');

    $cache->forget('laravel:pulse:servers-round-trip');
});
