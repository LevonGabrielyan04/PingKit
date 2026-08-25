<?php

use App\Data\HttpCheckResult;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\TransferStats;

test('fromResponse builds a result from a successful http response', function () {
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

    $result = HttpCheckResult::fromResponse('monitor-1', $response, $stats, $request, 'ignored');

    expect($result->monitorId)->toBe('monitor-1')
        ->and($result->statusCode)->toBe(200)
        ->and($result->responseTimeMs)->toBe(42)
        ->and($result->dnsTimeMs)->toBe(5)
        ->and($result->tcpTimeMs)->toBe(10)
        ->and($result->tlsTimeMs)->toBe(15)
        ->and($result->errorMessage)->toBeNull()
        ->and($result->responseHeaders)->toBe(['content-type' => 'text/html'])
        ->and($result->requestHeaders['user-agent'])->toBe('PingKit');
});

test('fromResponse keeps error message for non-success status codes', function () {
    $response = new Response(500, ['Content-Type' => 'text/plain'], 'fail');

    $result = HttpCheckResult::fromResponse('monitor-1', $response, null, null, 'Upstream failed');

    expect($result->statusCode)->toBe(500)
        ->and($result->errorMessage)->toBe('Upstream failed')
        ->and($result->responseTimeMs)->toBe(0)
        ->and($result->requestHeaders)->toBeNull();
});

test('fromFailure builds a network error result', function () {
    $request = new Request('GET', 'https://example.com', ['X-Check' => '1']);

    $result = HttpCheckResult::fromFailure(
        'monitor-1',
        'Connection timed out',
        null,
        $request,
    );

    expect($result->statusCode)->toBe(HttpCheckResult::NETWORK_ERROR_STATUS_CODE)
        ->and($result->errorMessage)->toBe('Connection timed out')
        ->and($result->responseHeaders)->toBe([])
        ->and($result->requestHeaders['x-check'])->toBe('1');
});
