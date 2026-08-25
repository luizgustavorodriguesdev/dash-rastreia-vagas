<?php

namespace Database\Factories;

use App\Models\JobSource;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<JobSource> */
class JobSourceFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->company().' Jobs';

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'base_url' => fake()->url(),
            'is_active' => true,
            'last_synced_at' => null,
            'last_sync_error' => null,
        ];
    }
}
