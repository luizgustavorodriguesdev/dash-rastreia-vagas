<?php

namespace Database\Factories;

use App\Models\Technology;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Technology> */
class TechnologyFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'PHP', 'Laravel', 'JavaScript', 'TypeScript', 'React', 'Vue.js',
            'Node.js', 'Python', 'MySQL', 'PostgreSQL', 'Docker', 'AWS',
        ]).'-'.fake()->unique()->numberBetween(1, 99999);

        return ['name' => $name, 'slug' => Str::slug($name)];
    }
}
