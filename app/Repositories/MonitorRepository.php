<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\MonitorRepositoryInterface;
use App\Enums\HttpMethod;
use App\Models\Monitor;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
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
     * Stream httpable monitors in id-ordered chunks for outbound request building.
     *
     * @return LazyCollection<int, Monitor>
     */
    public function lazyHttpableById(int $chunkSize = 100): LazyCollection
    {
        return Monitor::query()
            ->select(['id', 'url_address', 'ip_address', 'request_method', 'request_headers'])
            ->where('is_httpable', true)
            ->lazyById($chunkSize);
    }

    /**
     * Return one id-ordered page of httpable monitor ids after an optional cursor.
     *
     * @return Collection<int, string>
     */
    public function httpableIdsAfterId(?string $afterId, int $limit): Collection
    {
        return $this->httpableAfterIdQuery($afterId, $limit)
            ->pluck('id');
    }

    /**
     * Return one id-ordered page of httpable monitors after an optional cursor.
     *
     * @return Collection<int, Monitor>
     */
    public function httpablePageAfterId(?string $afterId, int $limit): Collection
    {
        return $this->httpableAfterIdQuery($afterId, $limit)
            ->select(['id', 'url_address', 'ip_address', 'request_method', 'request_headers'])
            ->get();
    }

    /**
     * Stamp checked_at for the given monitor ids.
     *
     * @param  list<string>  $monitorIds
     */
    public function markCheckedAt(array $monitorIds): int
    {
        if ($monitorIds === []) {
            return 0;
        }

        return Monitor::query()
            ->whereIn('id', $monitorIds)
            ->update(['checked_at' => now()]);
    }

    /**
     * @return Builder<Monitor>
     */
    private function httpableAfterIdQuery(?string $afterId, int $limit): Builder
    {
        return Monitor::query()
            ->where('is_httpable', true)
            ->when($afterId !== null, fn (Builder $query): Builder => $query->where('id', '>', $afterId))
            ->orderBy('id')
            ->limit($limit);
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
