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
}
