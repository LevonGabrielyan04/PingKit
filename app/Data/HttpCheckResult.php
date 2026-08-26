<?php

declare(strict_types=1);

namespace App\Data;

use GuzzleHttp\TransferStats;
use Illuminate\Support\Str;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final readonly class HttpCheckResult
{
    /**
     * Status code stored when no HTTP response is received (connect/timeout/DNS errors).
     */
    public const int NETWORK_ERROR_STATUS_CODE = 599;

    /**
     * @param  array<string, string>  $responseHeaders
     * @param  array<string, string>|null  $requestHeaders
     */
    private function __construct(
        public string $monitorId,
        public int $statusCode,
        public int $responseTimeMs,
        public ?int $dnsTimeMs,
        public ?int $tcpTimeMs,
        public ?int $tlsTimeMs,
        public ?string $errorMessage,
        public array $responseHeaders,
        public ?array $requestHeaders,
    ) {}

    public static function fromResponse(
        string $monitorId,
        ResponseInterface $response,
        ?TransferStats $stats,
        ?RequestInterface $request,
        ?string $errorMessage = null,
    ): self {
        $statusCode = $response->getStatusCode();
        $timings = self::timingsFromStats($stats);

        if ($statusCode >= 200 && $statusCode <= 299) {
            $errorMessage = null;
        }

        return new self(
            monitorId: $monitorId,
            statusCode: $statusCode,
            responseTimeMs: $timings['response_time_ms'],
            dnsTimeMs: $timings['dns_time_ms'],
            tcpTimeMs: $timings['tcp_time_ms'],
            tlsTimeMs: $timings['tls_time_ms'],
            errorMessage: $errorMessage,
            responseHeaders: self::normalizeHeaders($response->getHeaders()),
            requestHeaders: $request === null ? null : self::normalizeHeaders($request->getHeaders()),
        );
    }

    public static function fromFailure(
        string $monitorId,
        string $errorMessage,
        ?TransferStats $stats,
        ?RequestInterface $request,
    ): self {
        $timings = self::timingsFromStats($stats);

        return new self(
            monitorId: $monitorId,
            statusCode: self::NETWORK_ERROR_STATUS_CODE,
            responseTimeMs: $timings['response_time_ms'],
            dnsTimeMs: $timings['dns_time_ms'],
            tcpTimeMs: $timings['tcp_time_ms'],
            tlsTimeMs: $timings['tls_time_ms'],
            errorMessage: Str::limit($errorMessage, 3000, ''),
            responseHeaders: [],
            requestHeaders: $request === null ? null : self::normalizeHeaders($request->getHeaders()),
        );
    }

    /**
     * @return array{
     *     response_time_ms: int,
     *     dns_time_ms: int|null,
     *     tcp_time_ms: int|null,
     *     tls_time_ms: int|null,
     * }
     */
    private static function timingsFromStats(?TransferStats $stats): array
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
        $nameLookup = self::secondsToMs($stats->getHandlerStat('namelookup_time'));
        $connect = self::secondsToMs($stats->getHandlerStat('connect_time'));
        $appConnect = self::secondsToMs($stats->getHandlerStat('appconnect_time'));

        $dnsTimeMs = $nameLookup;
        $tcpTimeMs = ($nameLookup !== null && $connect !== null && $connect >= $nameLookup)
            ? $connect - $nameLookup
            : null;
        $tlsTimeMs = ($appConnect !== null && $connect !== null && $appConnect > $connect)
            ? $appConnect - $connect
            : null;

        return [
            'response_time_ms' => $transferTime === null ? 0 : (int) round($transferTime * 1000),
            'dns_time_ms' => self::clampSmallInt($dnsTimeMs),
            'tcp_time_ms' => self::clampSmallInt($tcpTimeMs),
            'tls_time_ms' => self::clampSmallInt($tlsTimeMs),
        ];
    }

    private static function secondsToMs(mixed $seconds): ?int
    {
        if (! is_numeric($seconds)) {
            return null;
        }

        return (int) round(((float) $seconds) * 1000);
    }

    private static function clampSmallInt(?int $value): ?int
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
    private static function normalizeHeaders(array $headers): array
    {
        $normalized = [];

        foreach ($headers as $name => $values) {
            $normalized[strtolower((string) $name)] = implode(', ', $values);
        }

        return $normalized;
    }
}
