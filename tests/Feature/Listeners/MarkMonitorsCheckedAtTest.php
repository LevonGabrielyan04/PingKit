<?php

use App\Contracts\ChunkedRequestProviderInterface;
use App\Contracts\MonitorRepositoryInterface;
use App\Data\HttpCheckResult;
use App\Events\HttpChecksCompleted;
use App\Listeners\MarkMonitorsCheckedAt;
use App\Models\Monitor;
use App\Services\HttpCheckPoolService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Event;

test('it marks processed monitors checked_at from HttpChecksCompleted', function () {
    $checked = Monitor::factory()->create([
        'url_address' => 'https://example.com/checked',
        'is_httpable' => true,
        'checked_at' => null,
    ]);

    $untouched = Monitor::factory()->create([
        'url_address' => 'https://example.com/untouched',
        'is_httpable' => true,
        'checked_at' => null,
    ]);

    $listener = new MarkMonitorsCheckedAt(app(MonitorRepositoryInterface::class));

    $listener->handle(new HttpChecksCompleted([
        $checked->id => HttpCheckResult::fromFailure($checked->id, 'n/a', null, null),
    ]));

    expect($checked->fresh()->checked_at)->not->toBeNull()
        ->and($untouched->fresh()->checked_at)->toBeNull();
});

test('HttpChecksCompleted listener is registered', function () {
    Event::fake();

    Event::assertListening(
        HttpChecksCompleted::class,
        MarkMonitorsCheckedAt::class,
    );
});

test('execute updates checked_at for processed monitors', function () {
    $monitor = Monitor::factory()->create([
        'url_address' => 'https://example.com/pool-checked',
        'is_httpable' => true,
        'checked_at' => null,
    ]);

    $mock = new MockHandler([
        new Response(200),
    ]);

    $service = new HttpCheckPoolService(
        app(ChunkedRequestProviderInterface::class),
        new Client(['handler' => HandlerStack::create($mock)]),
    );

    $service->execute();

    expect($monitor->fresh()->checked_at)->not->toBeNull();
});
