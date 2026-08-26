<?php

use App\Contracts\HttpCheckLogRepositoryInterface;
use App\Data\HttpCheckLogData;
use App\Data\HttpCheckResult;
use App\Models\HttpCheckLog;
use App\Models\Monitor;
use App\Models\User;
use App\Repositories\HttpCheckLogRepository;
use App\Services\HttpCheckPoolService;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\TransferStats;
use Illuminate\Pagination\Paginator;

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

test('writeLogs stores null response_headers when encoded JSON exceeds 5000 characters', function () {
    $monitor = Monitor::factory()->create(['is_httpable' => true]);

    $request = new Request('GET', 'https://example.com');
    $response = new Response(200, ['X-Big' => str_repeat('a', 5000)], 'ok');

    app(HttpCheckLogRepositoryInterface::class)->writeLogs([
        $monitor->id => HttpCheckResult::fromResponse($monitor->id, $response, null, $request),
    ]);

    $log = HttpCheckLog::query()->sole();

    expect($log->response_headers)->toBeNull()
        ->and($log->status_code)->toBe(200);
});

test('paginateFailed returns only the users unsuccessful logs as dtos', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $monitor = Monitor::factory()->for($user)->create();
    $otherMonitor = Monitor::factory()->for($otherUser)->create();

    $failedNewer = HttpCheckLog::factory()->for($monitor)->failed(500, 'server error')->create([
        'created_at' => now()->subMinute(),
    ]);
    $failedOlder = HttpCheckLog::factory()->for($monitor)->failed(404, 'not found')->create([
        'created_at' => now()->subHour(),
    ]);
    HttpCheckLog::factory()->for($monitor)->create(['status_code' => 200]);
    HttpCheckLog::factory()->for($otherMonitor)->failed()->create();

    $page = app(HttpCheckLogRepositoryInterface::class)->paginateFailed($user, perPage: 1);

    expect($page->total())->toBe(2)
        ->and($page->count())->toBe(1)
        ->and($page->items()[0])->toBeInstanceOf(HttpCheckLogData::class)
        ->and($page->items()[0]->toArray())->toMatchArray([
            'id' => $failedNewer->id,
            'target' => $monitor->url_address,
            'status_code' => 500,
            'error_message' => 'server error',
        ])
        ->and($page->items()[0]->toArray())->not->toHaveKeys(['monitor_id', 'is_successful']);

    Paginator::currentPageResolver(fn (): int => 2);

    $secondPage = app(HttpCheckLogRepositoryInterface::class)->paginateFailed($user, perPage: 1);

    expect($secondPage->items()[0]->id)->toBe($failedOlder->id);
});

test('paginateFailed target prefers url_address and falls back to ip_address', function () {
    $user = User::factory()->create();
    $urlMonitor = Monitor::factory()->for($user)->create([
        'url_address' => 'https://example.com',
        'ip_address' => null,
    ]);
    $ipMonitor = Monitor::factory()->for($user)->ipAddress('192.0.2.10')->create();

    HttpCheckLog::factory()->for($urlMonitor)->failed()->create([
        'created_at' => now()->subMinute(),
    ]);
    HttpCheckLog::factory()->for($ipMonitor)->failed()->create([
        'created_at' => now()->subHour(),
    ]);

    $page = app(HttpCheckLogRepositoryInterface::class)->paginateFailed($user, perPage: 15);

    expect($page->items()[0]->target)->toBe('https://example.com')
        ->and($page->items()[1]->target)->toBe('192.0.2.10');
});
