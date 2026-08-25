<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ChunkedRequestProviderInterface;
use App\Contracts\MonitorRepositoryInterface;
use App\Models\Monitor;
use Generator;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ChunkedRequestProvider implements ChunkedRequestProviderInterface
{
    private readonly int $maxChunkSize;

    public function __construct(private MonitorRepositoryInterface $monitors)
    {
        $this->maxChunkSize = (int) config('monitors.max_chunk_size');
    }

    /**
     * Yield Guzzle Pool–ready requests for httpable monitors, loaded in chunks.
     *
     * @return Generator<string, Request>
     */
    public function requests(int $chunkSize = 100): Generator
    {
        $this->assertChunkSize($chunkSize);

        return $this->yieldRequests($chunkSize);
    }

    /**
     * Yield Guzzle Pool–ready requests for a single httpable monitor page.
     *
     * @return Generator<string, Request>
     */
    public function requestsPage(?string $afterId, int $limit): Generator
    {
        $this->assertChunkSize($limit);

        return $this->yieldRequestsPage($afterId, $limit);
    }

    /**
     * @return Generator<string, Request>
     */
    private function yieldRequests(int $chunkSize): Generator
    {
        foreach ($this->monitors->lazyHttpableById($chunkSize) as $monitor) {
            yield $monitor->id => $this->toRequest($monitor);
        }
    }

    /**
     * @return Generator<string, Request>
     */
    private function yieldRequestsPage(?string $afterId, int $limit): Generator
    {
        foreach ($this->monitors->httpablePageAfterId($afterId, $limit) as $monitor) {
            yield $monitor->id => $this->toRequest($monitor);
        }
    }

    private function assertChunkSize(int $chunkSize): void
    {
        if ($chunkSize > $this->maxChunkSize) {
            throw ValidationException::withMessages([
                'chunkSize' => "Chunk size must be no more than {$this->maxChunkSize}.",
            ]);
        }
    }

    private function toRequest(Monitor $monitor): Request
    {
        return new Request(
            $monitor->request_method->label(),
            $this->targetUri($monitor),
            $this->headers($monitor),
        );
    }

    private function targetUri(Monitor $monitor): string
    {
        if ($monitor->url_address !== null) {
            return $monitor->url_address;
        }

        $ip = $monitor->ip_address;

        if (Str::contains($ip, ':')) {
            return 'http://['.$ip.']';
        }

        return 'http://'.$ip;
    }

    /**
     * @return array<string, string>
     */
    private function headers(Monitor $monitor): array
    {
        if ($monitor->request_headers === null) {
            return [];
        }

        return collect($monitor->request_headers)
            ->map(fn (mixed $value): string => (string) $value)
            ->all();
    }
}
