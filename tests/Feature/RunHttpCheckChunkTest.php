<?php

use App\Contracts\ChunkedRequestProviderInterface;
use App\Contracts\HttpCheckLogRepositoryInterface;
use App\Jobs\RunHttpCheckChunk;
use App\Models\HttpCheckLog;
use App\Models\Monitor;
use App\Models\User;
use App\Services\HttpCheckPoolService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

test('it polls one cursor page and writes check logs', function () {
    $user = User::factory()->create();

    $monitors = Monitor::factory()->for($user)->count(3)->create([
        'url_address' => 'https://example.com/status',
        'is_httpable' => true,
    ])->sortBy('id')->values();

    $mock = new MockHandler([
        new Response(200, ['Content-Type' => 'text/plain'], 'ok'),
        new Response(200, ['Content-Type' => 'text/plain'], 'ok'),
    ]);

    $pool = new HttpCheckPoolService(
        app(ChunkedRequestProviderInterface::class),
        new Client(['handler' => HandlerStack::create($mock)]),
    );

    $job = new RunHttpCheckChunk(afterId: null, limit: 2);

    $job->handle($pool, app(HttpCheckLogRepositoryInterface::class));

    expect(HttpCheckLog::query()->count())->toBe(2)
        ->and(HttpCheckLog::query()->pluck('monitor_id')->sort()->values()->all())
        ->toBe([
            $monitors[0]->id,
            $monitors[1]->id,
        ]);
});

test('it uses the configured polls queue', function () {
    $job = new RunHttpCheckChunk(afterId: 'abc', limit: 50);

    expect($job->queue)->toBe(config('monitors.polls_queue'));
});
