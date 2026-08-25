<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Contracts\MonitorRepositoryInterface;
use App\Events\HttpChecksCompleted;

class MarkMonitorsCheckedAt
{
    public function __construct(
        private MonitorRepositoryInterface $monitors,
    ) {}

    public function handle(HttpChecksCompleted $event): void
    {
        $this->monitors->markCheckedAt(array_keys($event->results));
    }
}
