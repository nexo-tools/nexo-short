<?php

namespace Database\Factories;

use App\Models\Click;
use App\Models\Link;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Click>
 */
class ClickFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'link_id' => Link::factory(),
            'visitor_hash' => hash('sha256', (string) $this->faker->uuid()),
            'referrer_host' => $this->faker->optional()->domainName(),
            'device' => $this->faker->randomElement(['mobile', 'desktop', 'bot']),
            'country' => $this->faker->optional()->countryCode(),
            'created_at' => now(),
        ];
    }

    public function bot(): static
    {
        return $this->state(fn () => ['device' => 'bot']);
    }
}
