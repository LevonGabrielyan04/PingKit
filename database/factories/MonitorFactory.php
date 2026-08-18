<?php

namespace Database\Factories;

use App\Models\Monitor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Monitor>
 */
class MonitorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'url_address' => fake()->url(),
            'ip_address' => null,
            'request_headers' => null,
        ];
    }

    /**
     * Indicate that the monitor targets an IP address instead of a URL.
     */
    public function ipAddress(string $ip = '192.0.2.1'): static
    {
        return $this->state(fn (array $attributes): array => [
            'url_address' => null,
            'ip_address' => $ip,
        ]);
    }
}
