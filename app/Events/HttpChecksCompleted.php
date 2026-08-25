<?php

declare(strict_types=1);

namespace App\Events;

use App\Data\HttpCheckResult;
use Illuminate\Foundation\Events\Dispatchable;

class HttpChecksCompleted
{
    use Dispatchable;

    /**
     * @param  array<string, HttpCheckResult>  $results
     */
    public function __construct(
        public array $results,
    ) {}
}
