<?php

use App\Models\HttpCheckLog;
use App\Models\Monitor;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('authenticated users see only their failed http check logs as dto arrays', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $monitor = Monitor::factory()->for($user)->create();
    $otherMonitor = Monitor::factory()->for($otherUser)->create();

    $failed = HttpCheckLog::factory()->for($monitor)->failed(503, 'upstream unavailable')->create([
        'response_time_ms' => 120,
        'dns_time_ms' => 5,
        'tcp_time_ms' => 10,
        'tls_time_ms' => 15,
        'response_headers' => ['content-type' => 'text/plain'],
    ]);
    HttpCheckLog::factory()->for($monitor)->create(['status_code' => 200]);
    HttpCheckLog::factory()->for($otherMonitor)->failed()->create();

    $this->actingAs($user)
        ->get(route('http'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Http')
            ->has('logs', 1)
            ->where('logs.0.id', $failed->id)
            ->where('logs.0.target', $monitor->url_address)
            ->where('logs.0.status_code', 503)
            ->where('logs.0.response_time_ms', 120)
            ->where('logs.0.dns_time_ms', 5)
            ->where('logs.0.tcp_time_ms', 10)
            ->where('logs.0.tls_time_ms', 15)
            ->where('logs.0.error_message', 'upstream unavailable')
            ->where('logs.0.response_headers', ['content-type' => 'text/plain'])
            ->where('logs.0.created_at', $failed->created_at->toIso8601String())
            ->missing('logs.0.monitor_id')
            ->missing('logs.0.is_successful')
        );
});

test('guests are redirected to the login page from the http check log controller', function () {
    $this->get(route('http'))
        ->assertRedirect(route('login'));
});
