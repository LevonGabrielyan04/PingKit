<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\HttpCheckLogRepositoryInterface;
use App\Services\HttpCheckPoolService;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class RunHttpCheckChunk implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @var list<int>
     */
    public array $backoff = [1, 5, 10];

    public int $timeout = 60;

    public int $uniqueFor = 120;

    public function __construct(
        public ?string $afterId,
        public int $limit,
    ) {
        $this->onQueue((string) config('monitors.polls_queue'));
    }

    public function handle(
        HttpCheckPoolService $pool,
        HttpCheckLogRepositoryInterface $logs,
    ): void {
        $results = $pool->executePage($this->afterId, $this->limit);

        $logs->writeLogs($results);
    }

    public function uniqueId(): string
    {
        return 'http-check-chunk:'.($this->afterId ?? 'start').':'.$this->limit;
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Failed to run HTTP check chunk.', [
            'after_id' => $this->afterId,
            'limit' => $this->limit,
            'exception' => $exception?->getMessage(),
        ]);
    }
}
