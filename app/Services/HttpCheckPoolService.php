<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ChunkedRequestProviderInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\TransferStats;
use Illuminate\Support\Str;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class HttpCheckPoolService
{
    /**
     * Status code stored when no HTTP response is received (connect/timeout/DNS errors).
     */
    public const int NETWORK_ERROR_STATUS_CODE = 599;

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
     * @return array<string, array{
     *     monitor_id: string,
     *     status_code: int,
     *     response_time_ms: int,
     *     dns_time_ms: int|null,
     *     tcp_time_ms: int|null,
     *     tls_time_ms: int|null,
     *     error_message: string|null,
     *     response_headers: array<string, mixed>,
     *     request_headers: array<string, mixed>|null,
     * }>
     */
    public function execute(int $chunkSize = 100): array
    {
        $results = [];
        /** @var array<string, RequestInterface> $requestsByMonitor */
        $requestsByMonitor = [];
        /** @var array<string, TransferStats> $transferStats */
        $transferStats = [];

        $requests = function () use ($chunkSize, &$requestsByMonitor, &$transferStats) {
            foreach ($this->requestProvider->requests($chunkSize) as $monitorId => $request) {
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
                $results[$monitorId] = $this->resultFromResponse(
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
                        $results[$monitorId] = $this->resultFromResponse(
                            $monitorId,
                            $response,
                            $stats,
                            $request,
                            $reason->getMessage(),
                        );

                        return;
                    }
                }

                $results[$monitorId] = $this->resultFromFailure(
                    $monitorId,
                    $reason instanceof Throwable ? $reason->getMessage() : (string) $reason,
                    $stats,
                    $request,
                );
            },
        ]);

        $pool->promise()->wait();

        return $results;
    }

    /**
     * @return array{
     *     monitor_id: string,
     *     status_code: int,
     *     response_time_ms: int,
     *     dns_time_ms: int|null,
     *     tcp_time_ms: int|null,
     *     tls_time_ms: int|null,
     *     error_message: string|null,
     *     response_headers: array<string, mixed>,
     *     request_headers: array<string, mixed>|null,
     * }
     */
    private function resultFromResponse(
        string $monitorId,
        ResponseInterface $response,
        ?TransferStats $stats,
        ?RequestInterface $request,
        ?string $errorMessage = null,
    ): array {
        $statusCode = $response->getStatusCode();
        $timings = $this->timingsFromStats($stats);

        if ($statusCode >= 200 && $statusCode <= 299) {
            $errorMessage = null;
        }

        return [
            'monitor_id' => $monitorId,
            'status_code' => $statusCode,
            'response_time_ms' => $timings['response_time_ms'],
            'dns_time_ms' => $timings['dns_time_ms'],
            'tcp_time_ms' => $timings['tcp_time_ms'],
            'tls_time_ms' => $timings['tls_time_ms'],
            'error_message' => $errorMessage,
            'response_headers' => $this->normalizeHeaders($response->getHeaders()),
            'request_headers' => $request === null ? null : $this->normalizeHeaders($request->getHeaders()),
        ];
    }

    /**
     * @return array{
     *     monitor_id: string,
     *     status_code: int,
     *     response_time_ms: int,
     *     dns_time_ms: int|null,
     *     tcp_time_ms: int|null,
     *     tls_time_ms: int|null,
     *     error_message: string|null,
     *     response_headers: array<string, mixed>,
     *     request_headers: array<string, mixed>|null,
     * }
     */
    private function resultFromFailure(
        string $monitorId,
        string $errorMessage,
        ?TransferStats $stats,
        ?RequestInterface $request,
    ): array {
        $timings = $this->timingsFromStats($stats);

        return [
            'monitor_id' => $monitorId,
            'status_code' => self::NETWORK_ERROR_STATUS_CODE,
            'response_time_ms' => $timings['response_time_ms'],
            'dns_time_ms' => $timings['dns_time_ms'],
            'tcp_time_ms' => $timings['tcp_time_ms'],
            'tls_time_ms' => $timings['tls_time_ms'],
            'error_message' => Str::limit($errorMessage, 255, ''),
            'response_headers' => [],
            'request_headers' => $request === null ? null : $this->normalizeHeaders($request->getHeaders()),
        ];
    }

    /**
     * @return array{
     *     response_time_ms: int,
     *     dns_time_ms: int|null,
     *     tcp_time_ms: int|null,
     *     tls_time_ms: int|null,
     * }
     */
    private function timingsFromStats(?TransferStats $stats): array
    {
        if ($stats === null) {
            return [
                'response_time_ms' => 0,
                'dns_time_ms' => null,
                'tcp_time_ms' => null,
                'tls_time_ms' => null,
            ];
        }

        $transferTime = $stats->getTransferTime();
        $nameLookup = $this->secondsToMs($stats->getHandlerStat('namelookup_time'));
        $connect = $this->secondsToMs($stats->getHandlerStat('connect_time'));
        $appConnect = $this->secondsToMs($stats->getHandlerStat('appconnect_time'));

        $dnsTimeMs = $nameLookup;
        $tcpTimeMs = ($nameLookup !== null && $connect !== null && $connect >= $nameLookup)
            ? $connect - $nameLookup
            : null;
        $tlsTimeMs = ($appConnect !== null && $connect !== null && $appConnect > $connect)
            ? $appConnect - $connect
            : null;

        return [
            'response_time_ms' => $transferTime === null ? 0 : (int) round($transferTime * 1000),
            'dns_time_ms' => $this->clampSmallInt($dnsTimeMs),
            'tcp_time_ms' => $this->clampSmallInt($tcpTimeMs),
            'tls_time_ms' => $this->clampSmallInt($tlsTimeMs),
        ];
    }

    private function secondsToMs(mixed $seconds): ?int
    {
        if (! is_numeric($seconds)) {
            return null;
        }

        return (int) round(((float) $seconds) * 1000);
    }

    private function clampSmallInt(?int $value): ?int
    {
        if ($value === null) {
            return null;
        }

        return max(0, min(32767, $value));
    }

    /**
     * @param  array<string, array<int, string>>  $headers
     * @return array<string, string>
     */
    private function normalizeHeaders(array $headers): array
    {
        $normalized = [];

        foreach ($headers as $name => $values) {
            $normalized[strtolower((string) $name)] = implode(', ', $values);
        }

        return $normalized;
    }
}
