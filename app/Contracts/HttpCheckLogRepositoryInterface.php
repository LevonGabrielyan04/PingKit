<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\HttpCheckLogData;
use App\Data\HttpCheckResult;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface HttpCheckLogRepositoryInterface
{
    /**
     * Format pool results and insert them into http_check_logs.
     *
     * @param  array<string, HttpCheckResult>  $results
     */
    public function writeLogs(array $results): int;

    /**
     * Paginate unsuccessful HTTP check logs for the given user as DTOs.
     *
     * @return LengthAwarePaginator<int, HttpCheckLogData>
     */
    public function paginateFailed(User $user, int $perPage = 15): LengthAwarePaginator;

    /**
     * Delete successful HTTP check logs older than 48 hours.
     */
    public function deleteSuccessfulOlderThan48Hours(): int;

    /**
     * Delete all HTTP check logs older than one month.
     */
    public function deleteOlderThanOneMonth(): int;
}
