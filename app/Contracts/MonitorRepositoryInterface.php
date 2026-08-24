<?php

namespace App\Contracts;

use App\Models\Monitor;
use App\Models\User;
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
}
