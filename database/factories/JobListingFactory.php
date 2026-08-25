<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\JobListing;
use App\Models\JobSource;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<JobListing> */
class JobListingFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->jobTitle();
        $externalId = (string) Str::uuid();

        return [
            'job_source_id' => JobSource::factory(),
            'company_id' => Company::factory(),
            'external_id' => $externalId,
            'title' => $title,
            'category' => 'Software Development',
            'description' => fake()->paragraphs(3, true),
            'url' => fake()->url(),
            'employment_type' => fake()->randomElement(['full_time', 'contract', 'internship']),
            'workplace_type' => fake()->randomElement(['remote', 'hybrid', 'onsite']),
            'seniority' => fake()->randomElement(['junior', 'mid', 'senior']),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'country_code' => 'BR',
            'location_text' => 'Brasil',
            'salary_min' => 5000,
            'salary_max' => 9000,
            'salary_currency' => 'BRL',
            'salary_period' => 'month',
            'salary_text' => 'R$ 5.000 - R$ 9.000 por mes',
            'status' => JobListing::STATUS_ACTIVE,
            'content_hash' => hash('sha256', $title.$externalId),
            'raw_payload' => ['id' => $externalId, 'title' => $title],
            'published_at' => now()->subDays(fake()->numberBetween(0, 30)),
            'expires_at' => now()->addDays(30),
            'imported_at' => now(),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status' => JobListing::STATUS_EXPIRED,
            'expires_at' => now()->subDay(),
        ]);
    }
}
