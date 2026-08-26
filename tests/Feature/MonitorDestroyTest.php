<?php

use App\Models\Monitor;
use App\Models\User;

test('guests cannot delete monitors', function () {
    $monitor = Monitor::factory()->create();

    $this->delete(route('monitors.destroy', $monitor))
        ->assertRedirect(route('login'));

    expect(Monitor::query()->whereKey($monitor->id)->exists())->toBeTrue();
});

test('authenticated users can delete their monitor', function () {
    $user = User::factory()->create();
    $monitor = Monitor::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete(route('monitors.destroy', $monitor))
        ->assertRedirect(route('monitors.index'));

    expect(Monitor::query()->whereKey($monitor->id)->exists())->toBeFalse();
});

test('authenticated users cannot delete another users monitor', function () {
    $user = User::factory()->create();
    $monitor = Monitor::factory()->create();

    $this->actingAs($user)
        ->delete(route('monitors.destroy', $monitor))
        ->assertForbidden();

    expect(Monitor::query()->whereKey($monitor->id)->exists())->toBeTrue();
});
