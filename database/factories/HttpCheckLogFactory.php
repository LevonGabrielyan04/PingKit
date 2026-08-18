<?php

namespace Database\Factories;

use App\Models\HttpCheckLog;
use App\Models\Monitor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HttpCheckLog>
 */
class HttpCheckLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'monitor_id' => Monitor::factory(),
            'status_code' => 200,
            'response_time_ms' => fake()->numberBetween(10, 500),
            'dns_time_ms' => null,
            'tcp_time_ms' => null,
            'tls_time_ms' => null,
            'error_message' => null,
            'response_headers' => ['content-type' => 'text/html'],
            'request_headers' => null,
        ];
    }

    /**
     * Indicate that the check failed with a non-2xx status code.
     */
    public function failed(int $statusCode = 500, string $errorMessage = 'request failed'): static
    {
        return $this->state(fn (array $attributes): array => [
            'status_code' => $statusCode,
            'error_message' => $errorMessage,
        ]);
    }
}
