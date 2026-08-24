<?php

namespace App\Contracts;

use App\Models\Monitor;
use App\Models\User;
use Inertia\Response;

interface MonitorRepositoryInterface
{
    /**
     * Display the monitors page.
     */
    public function index(): Response;

    /**
     * Persist a new monitor for the given user.
     *
     * @param  array<string, mixed>  $data
     */
    public function store(User $user, array $data): Monitor;
}
