<?php

use App\Models\Monitor;
use App\Models\User;
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
