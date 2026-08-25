<?php

declare(strict_types=1);

namespace App\Contracts;

interface HttpCheckLogRepositoryInterface
{
    /**
     * Format pool results and insert them into http_check_logs.
     *
     * @param  array<string, array{
     *     monitor_id: string,
     *     status_code: int,
     *     response_time_ms: int,
     *     dns_time_ms: int|null,
     *     tcp_time_ms: int|null,
     *     tls_time_ms: int|null,
     *     error_message: string|null,
     *     response_headers: array<string, mixed>,
     *     request_headers: array<string, mixed>|null,
     * }>  $results
     */
    public function writeLogs(array $results): int;
}
