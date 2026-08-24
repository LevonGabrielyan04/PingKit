<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\MonitorRepositoryInterface;
use App\Enums\HttpMethod;
use App\Models\Monitor;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class MonitorRepository implements MonitorRepositoryInterface
{
    /**
     * Display the monitors page.
     */
    public function index(): Response
    {
        return Inertia::render('monitors/Index', [
            'httpMethods' => collect(HttpMethod::cases())
                ->map(fn (HttpMethod $method): array => [
                    'value' => $method->value,
                    'label' => $method->label(),
                ])
                ->values()
                ->all(),
        ]);
    }

    /**
     * Persist a new monitor for the given user.
     *
     * @param  array<string, mixed>  $data
     */
    public function store(User $user, array $data): Monitor
    {
        return $user->monitors()->create($data);
    }
}
