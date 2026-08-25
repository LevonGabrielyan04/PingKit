<?php

use App\Contracts\MonitorRepositoryInterface;
use App\Models\Monitor;
use App\Models\User;

test('httpableIdsAfterId returns one ordered page of httpable ids', function () {
    $user = User::factory()->create();

    $monitors = Monitor::factory()->for($user)->count(5)->create([
        'is_httpable' => true,
    ])->sortBy('id')->values();

    Monitor::factory()->for($user)->create([
        'is_httpable' => false,
    ]);

    $repository = app(MonitorRepositoryInterface::class);

    $firstPage = $repository->httpableIdsAfterId(null, 2);

    expect($firstPage)->toHaveCount(2)
        ->and($firstPage->all())->toBe([
            $monitors[0]->id,
            $monitors[1]->id,
        ]);

    $secondPage = $repository->httpableIdsAfterId($firstPage->last(), 2);

    expect($secondPage->all())->toBe([
        $monitors[2]->id,
        $monitors[3]->id,
    ]);

    $thirdPage = $repository->httpableIdsAfterId($secondPage->last(), 2);

    expect($thirdPage->all())->toBe([
        $monitors[4]->id,
    ]);
});

test('httpablePageAfterId returns request columns for one page', function () {
    $user = User::factory()->create();

    $first = Monitor::factory()->for($user)->create([
        'url_address' => 'https://example.com/a',
        'is_httpable' => true,
    ]);

    $second = Monitor::factory()->for($user)->create([
        'url_address' => 'https://example.com/b',
        'is_httpable' => true,
    ]);

    [$earlier, $later] = collect([$first, $second])->sortBy('id')->values()->all();

    $page = app(MonitorRepositoryInterface::class)
        ->httpablePageAfterId($earlier->id, 10);

    expect($page)->toHaveCount(1)
        ->and($page->first()->id)->toBe($later->id)
        ->and($page->first()->getAttributes())->toHaveKeys([
            'id',
            'url_address',
            'ip_address',
            'request_method',
            'request_headers',
        ])
        ->and($page->first()->getAttributes())->not->toHaveKey('is_httpable');
});

test('httpableIdsAfterId skips monitors checked within the last 60 seconds', function () {
    $user = User::factory()->create();

    $neverChecked = Monitor::factory()->for($user)->create([
        'is_httpable' => true,
        'checked_at' => null,
    ]);

    $stale = Monitor::factory()->for($user)->create([
        'is_httpable' => true,
        'checked_at' => now()->subSeconds(61),
    ]);

    Monitor::factory()->for($user)->create([
        'is_httpable' => true,
        'checked_at' => now()->subSeconds(30),
    ]);

    Monitor::factory()->for($user)->create([
        'is_httpable' => true,
        'checked_at' => now(),
    ]);

    $ids = app(MonitorRepositoryInterface::class)
        ->httpableIdsAfterId(null, 10);

    expect($ids->all())->toBe(
        collect([$neverChecked, $stale])->sortBy('id')->pluck('id')->all()
    );
});
