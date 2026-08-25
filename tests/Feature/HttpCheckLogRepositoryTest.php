<?php

use App\Contracts\HttpCheckLogRepositoryInterface;
use App\Data\HttpCheckResult;
use App\Models\HttpCheckLog;
use App\Models\Monitor;
use App\Repositories\HttpCheckLogRepository;
use App\Services\HttpCheckPoolService;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\TransferStats;

test('it is bound to the http check log repository interface', function () {
    expect(app(HttpCheckLogRepositoryInterface::class))
        ->toBeInstanceOf(HttpCheckLogRepository::class);
});

test('writeLogs formats results and inserts http check logs', function () {
    $monitor = Monitor::factory()->create(['is_httpable' => true]);

    $request = new Request('GET', 'https://example.com', ['User-Agent' => 'PingKit']);
    $response = new Response(200, ['Content-Type' => 'text/html'], 'ok');
    $stats = new TransferStats(
        $request,
        $response,
        0.042,
        null,
        [
            'namelookup_time' => 0.005,
            'connect_time' => 0.015,
            'appconnect_time' => 0.030,
        ],
    );

    $written = app(HttpCheckLogRepositoryInterface::class)->writeLogs([
        $monitor->id => HttpCheckResult::fromResponse($monitor->id, $response, $stats, $request),
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
        ->and($log->request_headers['user-agent'])->toBe('PingKit');
});

test('writeLogs stores failed checks with error messages', function () {
    $monitor = Monitor::factory()->create(['is_httpable' => true]);

    $request = new Request('GET', 'https://example.com');
    $stats = new TransferStats($request, null, 1.0);

    app(HttpCheckLogRepositoryInterface::class)->writeLogs([
        $monitor->id => HttpCheckResult::fromFailure(
            $monitor->id,
            'Connection timed out',
            $stats,
            null,
        ),
    ]);

    $log = HttpCheckLog::query()->sole();

    expect($log->status_code)->toBe(HttpCheckPoolService::NETWORK_ERROR_STATUS_CODE)
        ->and($log->is_successful)->toBeFalse()
        ->and($log->error_message)->toBe('Connection timed out')
        ->and($log->response_time_ms)->toBe(1000)
        ->and($log->response_headers)->toBe([]);
});

test('writeLogs returns zero when there are no results', function () {
    expect(app(HttpCheckLogRepositoryInterface::class)->writeLogs([]))->toBe(0)
        ->and(HttpCheckLog::query()->count())->toBe(0);
});
