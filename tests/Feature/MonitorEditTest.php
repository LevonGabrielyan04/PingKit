<?php

use App\Enums\HttpMethod;
use App\Models\Monitor;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected from the monitor edit page', function () {
    $monitor = Monitor::factory()->create();

    $this->get(route('monitors.edit', $monitor))
        ->assertRedirect(route('login'));
});

test('guests cannot see another users monitor', function () {
    $owner = User::factory()->create();
    $monitor = Monitor::factory()->for($owner)->create();

    $this->get(route('monitors.edit', $monitor))
        ->assertRedirect(route('login'));
});

test('authenticated users can visit the monitor edit page for their monitor', function () {
    $user = User::factory()->create();
    $monitor = Monitor::factory()->for($user)->create([
        'url_address' => 'https://example.com',
        'request_method' => HttpMethod::Post,
        'request_headers' => ['User-Agent' => 'PingKit'],
        'is_httpable' => false,
    ]);

    $this->actingAs($user)
        ->get(route('monitors.edit', $monitor))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('monitors/Edit')
            ->has('httpMethods', count(HttpMethod::cases()))
            ->where('monitor.id', $monitor->id)
            ->where('monitor.url_address', 'https://example.com')
            ->where('monitor.ip_address', null)
            ->where('monitor.request_method', HttpMethod::Post->value)
            ->where('monitor.request_headers', ['User-Agent' => 'PingKit'])
            ->where('monitor.is_httpable', false));
});

test('authenticated users cannot visit the edit page for another users monitor', function () {
    $user = User::factory()->create();
    $monitor = Monitor::factory()->create();

    $this->actingAs($user)
        ->get(route('monitors.edit', $monitor))
        ->assertForbidden();
});

test('guests cannot update monitors', function () {
    $monitor = Monitor::factory()->create([
        'url_address' => 'https://example.com',
    ]);

    $this->patch(route('monitors.update', $monitor), [
        'url_address' => 'https://changed.example',
        'request_method' => HttpMethod::Get->value,
    ])->assertRedirect(route('login'));

    expect($monitor->fresh()->url_address)->toBe('https://example.com');
});

test('authenticated users can update their monitor', function () {
    $user = User::factory()->create();
    $monitor = Monitor::factory()->for($user)->create([
        'url_address' => 'https://example.com',
        'request_method' => HttpMethod::Get,
        'is_httpable' => true,
    ]);

    $this->actingAs($user)
        ->patch(route('monitors.update', $monitor), [
            'ip_address' => '192.0.2.1',
            'request_method' => HttpMethod::Post->value,
            'request_headers' => ['User-Agent' => 'PingKit'],
            'is_httpable' => false,
        ])
        ->assertRedirect(route('monitors.index'));

    $monitor->refresh();

    expect($monitor->url_address)->toBeNull()
        ->and($monitor->ip_address)->toBe('192.0.2.1')
        ->and($monitor->request_method)->toBe(HttpMethod::Post)
        ->and($monitor->request_headers)->toBe(['User-Agent' => 'PingKit'])
        ->and($monitor->is_httpable)->toBeFalse();
});

test('authenticated users cannot update another users monitor', function () {
    $user = User::factory()->create();
    $monitor = Monitor::factory()->create([
        'url_address' => 'https://example.com',
    ]);

    $this->actingAs($user)
        ->patch(route('monitors.update', $monitor), [
            'url_address' => 'https://changed.example',
            'request_method' => HttpMethod::Get->value,
        ])
        ->assertForbidden();

    expect($monitor->fresh()->url_address)->toBe('https://example.com');
});

test('monitor update validates the request method', function () {
    $user = User::factory()->create();
    $monitor = Monitor::factory()->for($user)->create();

    $this->actingAs($user)
        ->from(route('monitors.edit', $monitor))
        ->patch(route('monitors.update', $monitor), [
            'url_address' => 'https://example.com',
            'request_method' => 999,
        ])
        ->assertRedirect(route('monitors.edit', $monitor))
        ->assertSessionHasErrors('request_method');
});
