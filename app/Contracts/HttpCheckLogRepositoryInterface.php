<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\HttpCheckResult;

interface HttpCheckLogRepositoryInterface
{
    /**
     * Format pool results and insert them into http_check_logs.
     *
     * @param  array<string, HttpCheckResult>  $results
     */
    public function writeLogs(array $results): int;

    /**
     * Delete successful HTTP check logs older than 48 hours.
     */
    public function deleteSuccessfulOlderThan48Hours(): int;

    /**
     * Delete all HTTP check logs older than one month.
     */
    public function deleteOlderThanOneMonth(): int;
}
