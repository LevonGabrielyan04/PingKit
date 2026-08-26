<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\HttpCheckLogRepositoryInterface;
use App\Data\HttpCheckLogData;
use App\Data\HttpCheckResult;
use App\Models\HttpCheckLog;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class HttpCheckLogRepository implements HttpCheckLogRepositoryInterface
{
    /**
     * Format pool results and insert them into http_check_logs.
     *
     * @param  array<string, HttpCheckResult>  $results
     */
    public function writeLogs(array $results): int
    {
        if ($results === []) {
            return 0;
        }

        $createdAt = now();

        $rows = array_map(function (HttpCheckResult $result) use ($createdAt): array {
            $statusCode = $result->statusCode;
            $errorMessage = $result->errorMessage;

            if ($statusCode >= 200 && $statusCode <= 299) {
                $errorMessage = null;
            } elseif ($errorMessage !== null) {
                $errorMessage = Str::limit($errorMessage, 3000, '');
            }

            $responseHeaders = json_encode($result->responseHeaders, JSON_THROW_ON_ERROR);

            if (mb_strlen($responseHeaders, 'UTF-8') > 5000) {
                $responseHeaders = null;
            }

            return [
                'id' => (string) Str::uuid7(),
                'monitor_id' => $result->monitorId,
                'created_at' => $createdAt,
                'status_code' => $statusCode,
                'response_time_ms' => $result->responseTimeMs,
                'dns_time_ms' => $result->dnsTimeMs,
                'tcp_time_ms' => $result->tcpTimeMs,
                'tls_time_ms' => $result->tlsTimeMs,
                'error_message' => $errorMessage,
                'response_headers' => $responseHeaders,
                'request_headers' => $result->requestHeaders === null
                    ? null
                    : json_encode($result->requestHeaders, JSON_THROW_ON_ERROR),
            ];
        }, array_values($results));

        HttpCheckLog::query()->insert($rows);

        return count($rows);
    }

    /**
     * Paginate unsuccessful HTTP check logs for the given user as DTOs.
     *
     * @return LengthAwarePaginator<int, HttpCheckLogData>
     */
    public function paginateFailed(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return HttpCheckLog::query()
            ->select([
                'id',
                'monitor_id',
                'created_at',
                'status_code',
                'response_time_ms',
                'dns_time_ms',
                'tcp_time_ms',
                'tls_time_ms',
                'error_message',
            ])
            ->with('monitor:id,url_address,ip_address')
            ->where('is_successful', false)
            ->whereHas(
                'monitor',
                fn (Builder $query): Builder => $query->where('user_id', $user->id),
            )
            ->latest('created_at')
            ->paginate($perPage)
            ->through(fn (HttpCheckLog $log): HttpCheckLogData => HttpCheckLogData::fromModel($log));
    }

    /**
     * Delete successful HTTP check logs older than 48 hours.
     */
    public function deleteSuccessfulOlderThan48Hours(): int
    {
        return HttpCheckLog::query()
            ->where('is_successful', true)
            ->where('created_at', '<', now()->subHours(48))
            ->delete();
    }

    /**
     * Delete all HTTP check logs older than one month.
     */
    public function deleteOlderThanOneMonth(): int
    {
        return HttpCheckLog::query()
            ->where('created_at', '<', now()->subMonth())
            ->delete();
    }
}
