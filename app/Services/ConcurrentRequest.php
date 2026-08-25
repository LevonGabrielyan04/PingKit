<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Monitor;
use Generator;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ConcurrentRequest
{
    /**
     * Yield Guzzle Pool–ready requests for httpable monitors, loaded in chunks.
     *
     * @return Generator<string, Request>
     */
    public function requests(int $chunkSize = 100): Generator
    {
        if ($chunkSize > 200) {
            throw ValidationException::withMessages([
                'chunkSize' => 'Chunk size must be no more than 200.',
            ]);
        }

        return $this->yieldRequests($chunkSize);
    }

    /**
     * @return Generator<string, Request>
     */
    private function yieldRequests(int $chunkSize): Generator
    {
        $monitors = Monitor::query()
            ->select(['id', 'url_address', 'ip_address', 'request_method', 'request_headers'])
            ->where('is_httpable', true)
            ->lazyById($chunkSize);

        foreach ($monitors as $monitor) {
            yield $monitor->id => $this->toRequest($monitor);
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
