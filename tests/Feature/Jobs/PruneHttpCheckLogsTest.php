<?php

use App\Contracts\HttpCheckLogRepositoryInterface;
use App\Jobs\PruneHttpCheckLogs;
use App\Models\HttpCheckLog;
use App\Models\Monitor;
use Illuminate\Support\Facades\Schedule;

test('it deletes successful http check logs older than 48 hours', function () {
    $monitor = Monitor::factory()->create();

    $recentSuccessful = HttpCheckLog::factory()->create([
        'monitor_id' => $monitor->id,
        'status_code' => 200,
    ]);
    $oldSuccessful = HttpCheckLog::factory()->create([
        'monitor_id' => $monitor->id,
        'status_code' => 200,
    ]);
    $oldFailed = HttpCheckLog::factory()->failed()->create([
        'monitor_id' => $monitor->id,
    ]);

    HttpCheckLog::query()->whereKey($oldSuccessful->id)->update([
        'created_at' => now()->subHours(49),
    ]);
    HttpCheckLog::query()->whereKey($oldFailed->id)->update([
        'created_at' => now()->subHours(49),
    ]);

    (new PruneHttpCheckLogs)->handle(app(HttpCheckLogRepositoryInterface::class));

    expect(HttpCheckLog::query()->whereKey($recentSuccessful->id)->exists())->toBeTrue()
        ->and(HttpCheckLog::query()->whereKey($oldSuccessful->id)->exists())->toBeFalse()
        ->and(HttpCheckLog::query()->whereKey($oldFailed->id)->exists())->toBeTrue();
});

test('it deletes all http check logs older than one month', function () {
    $monitor = Monitor::factory()->create();

    $recentFailed = HttpCheckLog::factory()->failed()->create([
        'monitor_id' => $monitor->id,
    ]);
    $monthOldSuccessful = HttpCheckLog::factory()->create([
        'monitor_id' => $monitor->id,
        'status_code' => 200,
    ]);
    $monthOldFailed = HttpCheckLog::factory()->failed()->create([
        'monitor_id' => $monitor->id,
    ]);

    HttpCheckLog::query()->whereKey($monthOldSuccessful->id)->update([
        'created_at' => now()->subMonth()->subDay(),
    ]);
    HttpCheckLog::query()->whereKey($monthOldFailed->id)->update([
        'created_at' => now()->subMonth()->subDay(),
    ]);

    (new PruneHttpCheckLogs)->handle(app(HttpCheckLogRepositoryInterface::class));

    expect(HttpCheckLog::query()->whereKey($recentFailed->id)->exists())->toBeTrue()
        ->and(HttpCheckLog::query()->whereKey($monthOldSuccessful->id)->exists())->toBeFalse()
        ->and(HttpCheckLog::query()->whereKey($monthOldFailed->id)->exists())->toBeFalse();
});

test('it is scheduled to run daily', function () {
    $scheduled = collect(Schedule::events())
        ->contains(fn ($event): bool => str_contains($event->description, PruneHttpCheckLogs::class)
            && $event->expression === '0 0 * * *');

    expect($scheduled)->toBeTrue();
});
