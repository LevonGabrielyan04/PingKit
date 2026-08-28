<?php

use App\Models\HttpCheckLog;
use App\Models\Monitor;
use App\Models\User;

test('guests are redirected to the login page from the http errors export', function () {
    $this->get(route('http.errors.export'))
        ->assertRedirect(route('login'));
});

test('authenticated users can download their failed http check logs as csv', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $monitor = Monitor::factory()->for($user)->create([
        'url_address' => 'https://example.com',
    ]);
    $otherMonitor = Monitor::factory()->for($otherUser)->create();

    $failed = HttpCheckLog::factory()->for($monitor)->failed(503, 'upstream unavailable')->create([
        'response_time_ms' => 120,
        'dns_time_ms' => 5,
        'tcp_time_ms' => 10,
        'tls_time_ms' => 15,
        'response_headers' => ['content-type' => 'text/plain'],
        'created_at' => '2026-08-28 10:15:30',
    ]);
    HttpCheckLog::factory()->for($monitor)->create(['status_code' => 200]);
    HttpCheckLog::factory()->for($otherMonitor)->failed()->create();

    $response = $this->actingAs($user)
        ->get(route('http.errors.export'));

    $response->assertOk()
        ->assertDownload('http-errors.csv');

    $csv = $response->streamedContent();

    expect($csv)
        ->toContain('Checked at')
        ->and($csv)->toContain('Target')
        ->and($csv)->toContain('Total (ms)')
        ->and($csv)->toContain('https://example.com')
        ->and($csv)->toContain('503')
        ->and($csv)->toContain('upstream unavailable')
        ->and($csv)->toContain('content-type')
        ->and($csv)->not->toContain($otherMonitor->url_address);
});
