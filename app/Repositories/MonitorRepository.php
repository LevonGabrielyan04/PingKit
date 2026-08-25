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
     * Display the monitors list page.
     */
    public function index(User $user): Response
    {
        return Inertia::render('monitors/Index', [
            'monitors' => $user->monitors()
                ->select(['id', 'url_address', 'ip_address', 'request_method', 'is_httpable'])
                ->latest()
                ->get()
                ->map(fn (Monitor $monitor): array => [
                    'id' => $monitor->id,
                    'url_address' => $monitor->url_address,
                    'ip_address' => $monitor->ip_address,
                    'request_method' => $monitor->request_method->label(),
                    'is_httpable' => $monitor->is_httpable,
                ])
                ->values()
                ->all(),
        ]);
    }

    /**
     * Display the monitor creation page.
     */
    public function create(): Response
    {
        return Inertia::render('monitors/Create', [
            'httpMethods' => $this->httpMethods(),
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

    /**
     * Display the monitor edit page.
     */
    public function edit(Monitor $monitor): Response
    {
        return Inertia::render('monitors/Edit', [
            'httpMethods' => $this->httpMethods(),
            'monitor' => [
                'id' => $monitor->id,
                'url_address' => $monitor->url_address,
                'ip_address' => $monitor->ip_address,
                'request_method' => $monitor->request_method->value,
                'request_headers' => $monitor->request_headers,
                'is_httpable' => $monitor->is_httpable,
            ],
        ]);
    }

    /**
     * Persist updates for the given monitor.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Monitor $monitor, array $data): Monitor
    {
        $monitor->update($data);

        return $monitor->refresh();
    }

    /**
     * @return list<array{value: int, label: string}>
     */
    private function httpMethods(): array
    {
        return array_map(
            fn (HttpMethod $method): array => [
                'value' => $method->value,
                'label' => $method->label(),
            ],
            HttpMethod::cases(),
        );
    }
}
