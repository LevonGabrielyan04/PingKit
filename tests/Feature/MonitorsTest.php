<?php

use App\Enums\HttpMethod;
use App\Models\Monitor;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $this->get(route('monitors.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users can visit the monitors page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('monitors.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('monitors/Index')
            ->has('httpMethods', count(HttpMethod::cases()))
            ->where('httpMethods.0.label', HttpMethod::Get->label())
            ->where('httpMethods.0.value', HttpMethod::Get->value));
});

test('guests cannot store monitors', function () {
    $this->post(route('monitors.store'), [
        'url_address' => 'https://example.com',
        'request_method' => HttpMethod::Get->value,
    ])->assertRedirect(route('login'));

    expect(Monitor::query()->count())->toBe(0);
});

test('authenticated users can store a monitor with a url address', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('monitors.store'), [
            'url_address' => 'https://example.com',
            'request_method' => HttpMethod::Post->value,
            'request_headers' => ['User-Agent' => 'PingKit'],
            'is_httpable' => true,
        ])
        ->assertRedirect(route('monitors.index'));

    $monitor = Monitor::query()->sole();

    expect($monitor->user_id)->toBe($user->id)
        ->and($monitor->url_address)->toBe('https://example.com')
        ->and($monitor->ip_address)->toBeNull()
        ->and($monitor->request_method)->toBe(HttpMethod::Post)
        ->and($monitor->request_headers)->toBe(['User-Agent' => 'PingKit'])
        ->and($monitor->is_httpable)->toBeTrue();
});

test('authenticated users can store a monitor with an ip address', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('monitors.store'), [
            'ip_address' => '192.0.2.1',
            'request_method' => HttpMethod::Get->value,
        ])
        ->assertRedirect(route('monitors.index'));

    $monitor = Monitor::query()->sole();

    expect($monitor->user_id)->toBe($user->id)
        ->and($monitor->url_address)->toBeNull()
        ->and($monitor->ip_address)->toBe('192.0.2.1')
        ->and($monitor->request_method)->toBe(HttpMethod::Get)
        ->and($monitor->is_httpable)->toBeTrue();
});

test('monitor store requires exactly one of url or ip address', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('monitors.index'))
        ->post(route('monitors.store'), [
            'request_method' => HttpMethod::Get->value,
        ])
        ->assertRedirect(route('monitors.index'))
        ->assertSessionHasErrors(['url_address', 'ip_address']);

    $this->actingAs($user)
        ->from(route('monitors.index'))
        ->post(route('monitors.store'), [
            'url_address' => 'https://example.com',
            'ip_address' => '192.0.2.1',
            'request_method' => HttpMethod::Get->value,
        ])
        ->assertRedirect(route('monitors.index'))
        ->assertSessionHasErrors(['url_address', 'ip_address']);

    expect(Monitor::query()->count())->toBe(0);
});

test('monitor store validates the request method', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('monitors.index'))
        ->post(route('monitors.store'), [
            'url_address' => 'https://example.com',
            'request_method' => 999,
        ])
        ->assertRedirect(route('monitors.index'))
        ->assertSessionHasErrors('request_method');

    expect(Monitor::query()->count())->toBe(0);
});
