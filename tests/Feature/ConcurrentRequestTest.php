<?php

use App\Enums\HttpMethod;
use App\Models\Monitor;
use App\Models\User;
use App\Services\ConcurrentRequest;
use GuzzleHttp\Psr7\Request;
use Illuminate\Validation\ValidationException;

test('it yields guzzle pool requests for httpable monitors', function () {
    $user = User::factory()->create();

    $withUrl = Monitor::factory()->for($user)->create([
        'url_address' => 'https://example.com/status',
        'request_method' => HttpMethod::Head,
        'request_headers' => ['User-Agent' => 'PingKit'],
        'is_httpable' => true,
    ]);

    $withIp = Monitor::factory()->for($user)->ipAddress('192.0.2.10')->create([
        'request_method' => HttpMethod::Get,
        'is_httpable' => true,
    ]);

    Monitor::factory()->for($user)->create([
        'is_httpable' => false,
    ]);

    $requests = iterator_to_array((new ConcurrentRequest)->requests());

    expect($requests)->toHaveCount(2)
        ->and($requests)->toHaveKeys([$withUrl->id, $withIp->id])
        ->and($requests[$withUrl->id])->toBeInstanceOf(Request::class)
        ->and($requests[$withUrl->id]->getMethod())->toBe('HEAD')
        ->and((string) $requests[$withUrl->id]->getUri())->toBe('https://example.com/status')
        ->and($requests[$withUrl->id]->getHeaderLine('User-Agent'))->toBe('PingKit')
        ->and($requests[$withIp->id]->getMethod())->toBe('GET')
        ->and((string) $requests[$withIp->id]->getUri())->toBe('http://192.0.2.10');
});

test('it formats ipv6 monitor targets for guzzle', function () {
    $monitor = Monitor::factory()->ipAddress('2001:db8::1')->create([
        'is_httpable' => true,
    ]);

    $requests = iterator_to_array((new ConcurrentRequest)->requests());

    expect((string) $requests[$monitor->id]->getUri())->toBe('http://[2001:db8::1]');
});

test('it reads monitors in chunks while yielding requests', function () {
    $user = User::factory()->create();

    Monitor::factory()->for($user)->count(5)->create([
        'is_httpable' => true,
    ]);

    $requests = iterator_to_array((new ConcurrentRequest)->requests(chunkSize: 2));

    expect($requests)->toHaveCount(5)
        ->and(collect($requests)->every(fn ($request) => $request instanceof Request))->toBeTrue();
});

test('it rejects chunk sizes greater than 200', function () {
    (new ConcurrentRequest)->requests(chunkSize: 201);
})->throws(ValidationException::class, 'Chunk size must be no more than 200.');
