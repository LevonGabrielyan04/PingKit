<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\HttpCheckLogRepositoryInterface;
use App\Models\HttpCheckLog;
use Illuminate\Support\Str;

class HttpCheckLogRepository implements HttpCheckLogRepositoryInterface
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
    public function writeLogs(array $results): int
    {
        if ($results === []) {
            return 0;
        }

        $createdAt = now();

        $rows = array_map(function (array $result) use ($createdAt): array {
            $statusCode = $result['status_code'];
            $errorMessage = $result['error_message'];

            if ($statusCode >= 200 && $statusCode <= 299) {
                $errorMessage = null;
            } elseif ($errorMessage !== null) {
                $errorMessage = Str::limit($errorMessage, 255, '');
            }

            return [
                'id' => (string) Str::uuid7(),
                'monitor_id' => $result['monitor_id'],
                'created_at' => $createdAt,
                'status_code' => $statusCode,
                'response_time_ms' => $result['response_time_ms'],
                'dns_time_ms' => $result['dns_time_ms'],
                'tcp_time_ms' => $result['tcp_time_ms'],
                'tls_time_ms' => $result['tls_time_ms'],
                'error_message' => $errorMessage,
                'response_headers' => json_encode($result['response_headers'], JSON_THROW_ON_ERROR),
                'request_headers' => $result['request_headers'] === null
                    ? null
                    : json_encode($result['request_headers'], JSON_THROW_ON_ERROR),
            ];
        }, array_values($results));

        HttpCheckLog::query()->insert($rows);

        return count($rows);
    }
}
