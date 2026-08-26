<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\HttpCheckLog;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * @implements Arrayable<string, mixed>
 */
final readonly class HttpCheckLogData implements Arrayable, JsonSerializable
{
    private function __construct(
        public string $id,
        public string $target,
        public string $createdAt,
        public int $statusCode,
        public int $responseTimeMs,
        public ?int $dnsTimeMs,
        public ?int $tcpTimeMs,
        public ?int $tlsTimeMs,
        public ?string $errorMessage,
    ) {}

    public static function fromModel(HttpCheckLog $log): self
    {
        return new self(
            id: $log->id,
            target: $log->monitor->url_address ?? $log->monitor->ip_address ?? '',
            createdAt: $log->created_at->toIso8601String(),
            statusCode: $log->status_code,
            responseTimeMs: $log->response_time_ms,
            dnsTimeMs: $log->dns_time_ms,
            tcpTimeMs: $log->tcp_time_ms,
            tlsTimeMs: $log->tls_time_ms,
            errorMessage: $log->error_message,
        );
    }

    /**
     * @return array{
     *     id: string,
     *     target: string,
     *     created_at: string,
     *     status_code: int,
     *     response_time_ms: int,
     *     dns_time_ms: int|null,
     *     tcp_time_ms: int|null,
     *     tls_time_ms: int|null,
     *     error_message: string|null,
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'target' => $this->target,
            'created_at' => $this->createdAt,
            'status_code' => $this->statusCode,
            'response_time_ms' => $this->responseTimeMs,
            'dns_time_ms' => $this->dnsTimeMs,
            'tcp_time_ms' => $this->tcpTimeMs,
            'tls_time_ms' => $this->tlsTimeMs,
            'error_message' => $this->errorMessage,
        ];
    }

    /**
     * @return array{
     *     id: string,
     *     target: string,
     *     created_at: string,
     *     status_code: int,
     *     response_time_ms: int,
     *     dns_time_ms: int|null,
     *     tcp_time_ms: int|null,
     *     tls_time_ms: int|null,
     *     error_message: string|null,
     * }
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
