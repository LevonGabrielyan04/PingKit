<?php

use App\Contracts\HttpCheckLogRepositoryInterface;
use App\Models\HttpCheckLog;
use App\Models\Monitor;
use App\Repositories\HttpCheckLogRepository;
use App\Services\HttpCheckPoolService;

test('it is bound to the http check log repository interface', function () {
    expect(app(HttpCheckLogRepositoryInterface::class))
        ->toBeInstanceOf(HttpCheckLogRepository::class);
});

test('writeLogs formats results and inserts http check logs', function () {
    $monitor = Monitor::factory()->create(['is_httpable' => true]);

    $written = app(HttpCheckLogRepositoryInterface::class)->writeLogs([
        $monitor->id => [
            'monitor_id' => $monitor->id,
            'status_code' => 200,
            'response_time_ms' => 42,
            'dns_time_ms' => 5,
            'tcp_time_ms' => 10,
            'tls_time_ms' => 15,
            'error_message' => null,
            'response_headers' => ['content-type' => 'text/html'],
            'request_headers' => ['user-agent' => 'PingKit'],
        ],
    ]);

    expect($written)->toBe(1);

    $log = HttpCheckLog::query()->sole();

    expect($log->monitor_id)->toBe($monitor->id)
        ->and($log->status_code)->toBe(200)
        ->and($log->is_successful)->toBeTrue()
        ->and($log->response_time_ms)->toBe(42)
        ->and($log->dns_time_ms)->toBe(5)
        ->and($log->tcp_time_ms)->toBe(10)
        ->and($log->tls_time_ms)->toBe(15)
        ->and($log->error_message)->toBeNull()
        ->and($log->response_headers)->toBe(['content-type' => 'text/html'])
        ->and($log->request_headers)->toBe(['user-agent' => 'PingKit']);
});

test('writeLogs stores failed checks with error messages', function () {
    $monitor = Monitor::factory()->create(['is_httpable' => true]);

    app(HttpCheckLogRepositoryInterface::class)->writeLogs([
        $monitor->id => [
            'monitor_id' => $monitor->id,
            'status_code' => HttpCheckPoolService::NETWORK_ERROR_STATUS_CODE,
            'response_time_ms' => 1000,
            'dns_time_ms' => null,
            'tcp_time_ms' => null,
            'tls_time_ms' => null,
            'error_message' => 'Connection timed out',
            'response_headers' => [],
            'request_headers' => null,
        ],
    ]);

    $log = HttpCheckLog::query()->sole();

    expect($log->status_code)->toBe(599)
        ->and($log->is_successful)->toBeFalse()
        ->and($log->error_message)->toBe('Connection timed out')
        ->and($log->response_headers)->toBe([]);
});

test('writeLogs returns zero when there are no results', function () {
    expect(app(HttpCheckLogRepositoryInterface::class)->writeLogs([]))->toBe(0)
        ->and(HttpCheckLog::query()->count())->toBe(0);
});
