<?php

use App\Enums\HttpMethod;
use App\Models\Monitor;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

test('a monitor generates a version 7 uuid primary key', function () {
    $monitor = Monitor::factory()->create();

    expect($monitor->id)->toBeUuid()
        ->and(Str::isUuid($monitor->id, version: 7))->toBeTrue();

    $this->assertModelExists($monitor);
});

test('a monitor belongs to a user', function () {
    $user = User::factory()->create();
    $monitor = Monitor::factory()->for($user)->create();

    expect($monitor->user->is($user))->toBeTrue()
        ->and($user->monitors)->toHaveCount(1)
        ->and($user->monitors->first()->is($monitor))->toBeTrue();
});

test('a monitor can target an ip address', function () {
    $monitor = Monitor::factory()->ipAddress('192.0.2.1')->create();

    expect($monitor->url_address)->toBeNull()
        ->and($monitor->ip_address)->toBe('192.0.2.1');
});

test('a monitor casts request_method to the http method enum', function () {
    $monitor = Monitor::factory()->create([
        'request_method' => HttpMethod::Post,
    ]);

    $monitor->refresh();

    expect($monitor->request_method)->toBe(HttpMethod::Post);

    $this->assertDatabaseHas(Monitor::class, [
        'id' => $monitor->id,
        'request_method' => HttpMethod::Post->value,
    ]);
});

test('a monitor defaults request_method to get', function () {
    $monitor = Monitor::factory()->create();

    expect($monitor->request_method)->toBe(HttpMethod::Get);
});

test('a monitor defaults is_httpable to true', function () {
    $monitor = Monitor::factory()->create();

    expect($monitor->is_httpable)->toBeTrue();
});

test('a monitor defaults checked_at to null and casts it to carbon', function () {
    $monitor = Monitor::factory()->create();

    expect($monitor->checked_at)->toBeNull();

    $checkedAt = now()->startOfSecond();
    $monitor->update(['checked_at' => $checkedAt]);
    $monitor->refresh();

    expect($monitor->checked_at)->toBeInstanceOf(CarbonImmutable::class)
        ->and($monitor->checked_at->equalTo($checkedAt))->toBeTrue();
});
