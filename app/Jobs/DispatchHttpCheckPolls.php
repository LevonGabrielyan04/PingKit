<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\MonitorRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class DispatchHttpCheckPolls implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @var list<int>
     */
    public array $backoff = [1, 5, 10];

    public int $uniqueFor = 60;

    public function __construct()
    {
        $this->onQueue((string) config('monitors.polls_queue'));
    }

    public function handle(MonitorRepositoryInterface $monitors): void
    {
        $limit = min(
            (int) config('monitors.poll_chunk_size'),
            (int) config('monitors.max_chunk_size'),
        );
        $afterId = null;

        do {
            $ids = $monitors->httpableIdsAfterId($afterId, $limit);

            if ($ids->isEmpty()) {
                break;
            }

            RunHttpCheckChunk::dispatch($afterId, $limit);

            $afterId = $ids->last();
        } while ($ids->count() === $limit);
    }

    public function uniqueId(): string
    {
        return 'dispatch-http-check-polls';
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Failed to dispatch HTTP check poll chunks.', [
            'exception' => $exception?->getMessage(),
        ]);
    }
}
