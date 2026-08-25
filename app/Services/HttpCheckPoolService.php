<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ChunkedRequestProviderInterface;
use App\Data\HttpCheckResult;
use App\Events\HttpChecksCompleted;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\TransferStats;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class HttpCheckPoolService
{
    /**
     * Status code stored when no HTTP response is received (connect/timeout/DNS errors).
     */
    public const int NETWORK_ERROR_STATUS_CODE = HttpCheckResult::NETWORK_ERROR_STATUS_CODE;

    private readonly ClientInterface $client;

    private readonly int $concurrency;

    private readonly float $timeout;

    private readonly float $connectTimeout;

    public function __construct(
        private ChunkedRequestProviderInterface $requestProvider,
        ?ClientInterface $client = null,
    ) {
        $this->client = $client ?? new Client;
        $this->concurrency = (int) config('monitors.concurrency');
        $this->timeout = (float) config('monitors.timeout');
        $this->connectTimeout = (float) config('monitors.connect_timeout');
    }

    /**
     * Execute pooled HTTP checks for httpable monitors.
     *
     * @return array<string, HttpCheckResult>
     */
    public function execute(int $chunkSize = 100): array
    {
        return $this->runPool(
            $this->requestProvider->requests($chunkSize),
        );
    }

    /**
     * Execute pooled HTTP checks for a single httpable monitor page.
     *
     * @return array<string, HttpCheckResult>
     */
    public function executePage(?string $afterId, int $limit = 100): array
    {
        return $this->runPool(
            $this->requestProvider->requestsPage($afterId, $limit),
        );
    }

    /**
     * @param  iterable<string, RequestInterface>  $requestFactories
     * @return array<string, HttpCheckResult>
     */
    private function runPool(iterable $requestFactories): array
    {
        $results = [];
        /** @var array<string, RequestInterface> $requestsByMonitor */
        $requestsByMonitor = [];
        /** @var array<string, TransferStats> $transferStats */
        $transferStats = [];

        $requests = function () use ($requestFactories, &$requestsByMonitor, &$transferStats) {
            foreach ($requestFactories as $monitorId => $request) {
                $requestsByMonitor[$monitorId] = $request;

                yield $monitorId => function (array $options) use ($request, $monitorId, &$transferStats) {
                    $options['on_stats'] = function (TransferStats $stats) use ($monitorId, &$transferStats): void {
                        $transferStats[$monitorId] = $stats;
                    };

                    return $this->client->sendAsync($request, $options);
                };
            }
        };

        $pool = new Pool($this->client, $requests(), [
            'concurrency' => $this->concurrency,
            'options' => [
                'http_errors' => false,
                'timeout' => $this->timeout,
                'connect_timeout' => $this->connectTimeout,
                'allow_redirects' => false,
            ],
            'fulfilled' => function (ResponseInterface $response, string $monitorId) use (&$results, &$requestsByMonitor, &$transferStats): void {
                $results[$monitorId] = HttpCheckResult::fromResponse(
                    $monitorId,
                    $response,
                    $transferStats[$monitorId] ?? null,
                    $requestsByMonitor[$monitorId] ?? null,
                );
            },
            'rejected' => function (mixed $reason, string $monitorId) use (&$results, &$requestsByMonitor, &$transferStats): void {
                $stats = $transferStats[$monitorId] ?? null;
                $request = $requestsByMonitor[$monitorId] ?? null;

                if ($reason instanceof RequestException) {
                    $request ??= $reason->getRequest();
                    $response = $reason->getResponse();

                    if ($response !== null) {
                        $results[$monitorId] = HttpCheckResult::fromResponse(
                            $monitorId,
                            $response,
                            $stats,
                            $request,
                            $reason->getMessage(),
                        );

                        return;
                    }
                }

                $results[$monitorId] = HttpCheckResult::fromFailure(
                    $monitorId,
                    $reason instanceof Throwable ? $reason->getMessage() : (string) $reason,
                    $stats,
                    $request,
                );
            },
        ]);

        $pool->promise()->wait();

        HttpChecksCompleted::dispatch($results);

        return $results;
    }
}
