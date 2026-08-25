<?php

use App\Contracts\ChunkedRequestProviderInterface;
use App\Contracts\HttpCheckLogRepositoryInterface;
use App\Data\HttpCheckResult;
use App\Models\HttpCheckLog;
use App\Models\Monitor;
use App\Models\User;
use App\Services\HttpCheckPoolService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

test('execute runs pooled requests and returns results keyed by monitor id', function () {
    $user = User::factory()->create();

    $ok = Monitor::factory()->for($user)->create([
        'url_address' => 'https://example.com/ok',
        'request_headers' => ['User-Agent' => 'PingKit'],
        'is_httpable' => true,
    ]);

    $notFound = Monitor::factory()->for($user)->create([
        'url_address' => 'https://example.com/missing',
        'is_httpable' => true,
    ]);

    Monitor::factory()->for($user)->create([
        'is_httpable' => false,
    ]);

    $byUri = function (RequestInterface $request): Response {
        return match ((string) $request->getUri()) {
            'https://example.com/ok' => new Response(200, ['Content-Type' => 'text/html'], 'ok'),
            default => new Response(404, ['Content-Type' => 'application/json'], '{"error":"missing"}'),
        };
    };

    $mock = new MockHandler([$byUri, $byUri]);

    $service = new HttpCheckPoolService(
        app(ChunkedRequestProviderInterface::class),
        new Client([
            'handler' => HandlerStack::create($mock),
            'transfer_time' => 0.042,
        ]),
    );

    $results = $service->execute();

    expect($results)->toHaveCount(2)
        ->and($results)->toHaveKeys([$ok->id, $notFound->id])
        ->and($results[$ok->id])->toBeInstanceOf(HttpCheckResult::class)
        ->and($results[$ok->id]->statusCode)->toBe(200)
        ->and($results[$ok->id]->errorMessage)->toBeNull()
        ->and($results[$ok->id]->responseTimeMs)->toBe(42)
        ->and($results[$ok->id]->responseHeaders['content-type'])->toBe('text/html')
        ->and($results[$ok->id]->requestHeaders['user-agent'])->toBe('PingKit')
        ->and($results[$notFound->id]->statusCode)->toBe(404)
        ->and($results[$notFound->id]->errorMessage)->toBeNull();
});

test('execute records network failures with status 599', function () {
    $monitor = Monitor::factory()->create([
        'url_address' => 'https://example.com/down',
        'is_httpable' => true,
    ]);

    $mock = new MockHandler([
        new ConnectException('Connection timed out', new Request('GET', 'https://example.com/down')),
    ]);

    $service = new HttpCheckPoolService(
        app(ChunkedRequestProviderInterface::class),
        new Client(['handler' => HandlerStack::create($mock)]),
    );

    $results = $service->execute();

    expect($results[$monitor->id]->statusCode)->toBe(HttpCheckPoolService::NETWORK_ERROR_STATUS_CODE)
        ->and($results[$monitor->id]->errorMessage)->toBe('Connection timed out')
        ->and($results[$monitor->id]->responseHeaders)->toBe([]);
});

test('execute then writeLogs persists a full check cycle', function () {
    $monitor = Monitor::factory()->create([
        'url_address' => 'https://example.com/status',
        'request_headers' => ['X-Check' => '1'],
        'is_httpable' => true,
    ]);

    $mock = new MockHandler([
        new Response(201, ['X-From' => 'origin'], 'created'),
    ]);

    $service = new HttpCheckPoolService(
        app(ChunkedRequestProviderInterface::class),
        new Client([
            'handler' => HandlerStack::create($mock),
            'transfer_time' => 0.01,
        ]),
    );

    $written = app(HttpCheckLogRepositoryInterface::class)->writeLogs($service->execute());

    expect($written)->toBe(1);

    $log = HttpCheckLog::query()->sole();

    expect($log->monitor_id)->toBe($monitor->id)
        ->and($log->status_code)->toBe(201)
        ->and($log->is_successful)->toBeTrue()
        ->and($log->response_time_ms)->toBe(10)
        ->and($log->response_headers['x-from'])->toBe('origin')
        ->and($log->request_headers['x-check'])->toBe('1');
});

test('executePage only checks monitors after the cursor for one page', function () {
    $user = User::factory()->create();

    $monitors = Monitor::factory()->for($user)->count(3)->create([
        'url_address' => 'https://example.com/page',
        'is_httpable' => true,
    ])->sortBy('id')->values();

    $mock = new MockHandler([
        new Response(200),
    ]);

    $service = new HttpCheckPoolService(
        app(ChunkedRequestProviderInterface::class),
        new Client(['handler' => HandlerStack::create($mock)]),
    );

    $results = $service->executePage($monitors[0]->id, 1);

    expect($results)->toHaveCount(1)
        ->and($results)->toHaveKey($monitors[1]->id)
        ->and($results)->not->toHaveKey($monitors[0]->id)
        ->and($results)->not->toHaveKey($monitors[2]->id);
});
