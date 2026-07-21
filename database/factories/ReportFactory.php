<?php

namespace Database\Factories;

use App\Models\Report;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'slug' => Str::lower(Str::random(7)),
            'reason' => $this->faker->randomElement(['malicious', 'spam', 'abusive', 'broken', 'other']),
            'note' => $this->faker->optional()->sentence(),
            'created_at' => now(),
        ];
    }
}
