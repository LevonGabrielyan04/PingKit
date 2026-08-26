<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\HttpCheckLogRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class PruneHttpCheckLogs implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @var list<int>
     */
    public array $backoff = [1, 5, 10];

    public int $uniqueFor = 3600;

    public function handle(HttpCheckLogRepositoryInterface $logs): void
    {
        $logs->deleteSuccessfulOlderThan48Hours();
        $logs->deleteOlderThanOneMonth();
    }

    public function uniqueId(): string
    {
        return 'prune-http-check-logs';
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Failed to prune HTTP check logs.', [
            'exception' => $exception?->getMessage(),
        ]);
    }
}
