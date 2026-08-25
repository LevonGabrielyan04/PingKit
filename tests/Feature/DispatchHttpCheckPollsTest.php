<?php

use App\Contracts\MonitorRepositoryInterface;
use App\Jobs\DispatchHttpCheckPolls;
use App\Jobs\RunHttpCheckChunk;
use App\Models\Monitor;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

test('it dispatches cursor-paged chunk jobs onto the polls queue', function () {
    Queue::fake();

    $user = User::factory()->create();

    $monitors = Monitor::factory()->for($user)->count(5)->create([
        'is_httpable' => true,
    ])->sortBy('id')->values();

    Monitor::factory()->for($user)->create([
        'is_httpable' => false,
    ]);

    config(['monitors.poll_chunk_size' => 2]);

    (new DispatchHttpCheckPolls)->handle(app(MonitorRepositoryInterface::class));

    Queue::assertPushedOn(config('monitors.polls_queue'), RunHttpCheckChunk::class);
    Queue::assertPushed(RunHttpCheckChunk::class, 3);

    Queue::assertPushed(RunHttpCheckChunk::class, function (RunHttpCheckChunk $job): bool {
        return $job->afterId === null
            && $job->limit === 2;
    });

    Queue::assertPushed(RunHttpCheckChunk::class, function (RunHttpCheckChunk $job) use ($monitors): bool {
        return $job->afterId === $monitors[1]->id
            && $job->limit === 2;
    });

    Queue::assertPushed(RunHttpCheckChunk::class, function (RunHttpCheckChunk $job) use ($monitors): bool {
        return $job->afterId === $monitors[3]->id
            && $job->limit === 2;
    });
});

test('it dispatches nothing when there are no httpable monitors', function () {
    Queue::fake();

    Monitor::factory()->create([
        'is_httpable' => false,
    ]);

    (new DispatchHttpCheckPolls)->handle(app(MonitorRepositoryInterface::class));

    Queue::assertNothingPushed();
});
