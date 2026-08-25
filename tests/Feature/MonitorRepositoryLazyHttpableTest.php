<?php

use App\Contracts\MonitorRepositoryInterface;
use App\Models\Monitor;
use App\Models\User;

test('it streams only httpable monitors with request columns', function () {
    $user = User::factory()->create();

    $httpable = Monitor::factory()->for($user)->create([
        'is_httpable' => true,
    ]);

    Monitor::factory()->for($user)->create([
        'is_httpable' => false,
    ]);

    $monitors = app(MonitorRepositoryInterface::class)
        ->lazyHttpableById(2)
        ->all();

    expect($monitors)->toHaveCount(1)
        ->and($monitors[0]->id)->toBe($httpable->id)
        ->and($monitors[0]->getAttributes())->toHaveKeys([
            'id',
            'url_address',
            'ip_address',
            'request_method',
            'request_headers',
        ])
        ->and($monitors[0]->getAttributes())->not->toHaveKey('is_httpable');
});

test('it reads httpable monitors in chunks by id', function () {
    $user = User::factory()->create();

    Monitor::factory()->for($user)->count(5)->create([
        'is_httpable' => true,
    ]);

    $ids = app(MonitorRepositoryInterface::class)
        ->lazyHttpableById(2)
        ->pluck('id')
        ->all();

    expect($ids)->toHaveCount(5)
        ->and($ids)->toEqual(collect($ids)->sort()->values()->all());
});
