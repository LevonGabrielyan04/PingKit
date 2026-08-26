<?php

namespace App\Contracts;

use App\Models\Monitor;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Inertia\Response;

interface MonitorRepositoryInterface
{
    /**
     * Display the monitors list page.
     */
    public function index(User $user): Response;

    /**
     * Display the monitor creation page.
     */
    public function create(): Response;

    /**
     * Persist a new monitor for the given user.
     *
     * @param  array<string, mixed>  $data
     */
    public function store(User $user, array $data): Monitor;

    /**
     * Display the monitor edit page.
     */
    public function edit(Monitor $monitor): Response;

    /**
     * Persist updates for the given monitor.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Monitor $monitor, array $data): Monitor;

    /**
     * Delete the given monitor.
     */
    public function delete(Monitor $monitor): void;

    /**
     * Stream httpable monitors in id-ordered chunks for outbound request building.
     *
     * @return LazyCollection<int, Monitor>
     */
    public function lazyHttpableById(int $chunkSize = 100): LazyCollection;

    /**
     * Return one id-ordered page of httpable monitor ids after an optional cursor.
     *
     * @return Collection<int, string>
     */
    public function httpableIdsAfterId(?string $afterId, int $limit): Collection;

    /**
     * Return one id-ordered page of httpable monitors after an optional cursor.
     *
     * @return Collection<int, Monitor>
     */
    public function httpablePageAfterId(?string $afterId, int $limit): Collection;

    /**
     * Stamp checked_at for the given monitor ids.
     *
     * @param  list<string>  $monitorIds
     */
    public function markCheckedAt(array $monitorIds): int;
}
