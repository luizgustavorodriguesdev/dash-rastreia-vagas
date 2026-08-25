<?php

namespace App\Jobs\Importing\Data;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final readonly class ExternalJobData
{
    /**
     * @param  array{name: string, website_url?: ?string, logo_url?: ?string, location?: ?string}|null  $company
     * @param  list<string>  $technologies
     * @param  array<string, mixed>  $rawPayload
     */
    public function __construct(
        public string $externalId,
        public string $title,
        public string $url,
        public ?string $category,
        public ?string $description,
        public ?array $company,
        public array $technologies,
        public ?string $employmentType,
        public ?string $workplaceType,
        public ?string $seniority,
        public ?string $city,
        public ?string $state,
        public ?string $countryCode,
        public ?string $locationText,
        public ?float $salaryMin,
        public ?float $salaryMax,
        public ?string $salaryCurrency,
        public ?string $salaryPeriod,
        public ?string $salaryText,
        public ?string $publishedAt,
        public ?string $expiresAt,
        public array $rawPayload,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     *
     * @throws ValidationException
     */
    public static function fromArray(array $payload): self
    {
        $data = Validator::make($payload, [
            'external_id' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:2048'],
            'category' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'company' => ['nullable', 'array'],
            'company.name' => ['required_with:company', 'string', 'max:255'],
            'company.website_url' => ['nullable', 'url', 'max:255'],
            'company.logo_url' => ['nullable', 'url', 'max:255'],
            'company.location' => ['nullable', 'string', 'max:255'],
            'technologies' => ['sometimes', 'array'],
            'technologies.*' => ['string', 'max:100', 'distinct:ignore_case'],
            'employment_type' => ['nullable', 'string', 'max:40'],
            'workplace_type' => ['nullable', 'string', 'max:20'],
            'seniority' => ['nullable', 'string', 'max:40'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:100'],
            'country_code' => ['nullable', 'string', 'size:2'],
            'location_text' => ['nullable', 'string', 'max:255'],
            'salary_min' => ['nullable', 'numeric', 'min:0'],
            'salary_max' => ['nullable', 'numeric', 'gte:salary_min'],
            'salary_currency' => ['nullable', 'string', 'size:3'],
            'salary_period' => ['nullable', 'string', 'max:20'],
            'salary_text' => ['nullable', 'string', 'max:255'],
            'published_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:published_at'],
            '_raw_payload' => ['sometimes', 'array'],
        ])->validate();

        return new self(
            externalId: $data['external_id'],
            title: $data['title'],
            url: $data['url'],
            category: $data['category'] ?? null,
            description: $data['description'] ?? null,
            company: $data['company'] ?? null,
            technologies: array_values($data['technologies'] ?? []),
            employmentType: $data['employment_type'] ?? null,
            workplaceType: $data['workplace_type'] ?? null,
            seniority: $data['seniority'] ?? null,
            city: $data['city'] ?? null,
            state: $data['state'] ?? null,
            countryCode: isset($data['country_code']) ? strtoupper($data['country_code']) : null,
            locationText: $data['location_text'] ?? null,
            salaryMin: isset($data['salary_min']) ? (float) $data['salary_min'] : null,
            salaryMax: isset($data['salary_max']) ? (float) $data['salary_max'] : null,
            salaryCurrency: isset($data['salary_currency']) ? strtoupper($data['salary_currency']) : null,
            salaryPeriod: $data['salary_period'] ?? null,
            salaryText: $data['salary_text'] ?? null,
            publishedAt: $data['published_at'] ?? null,
            expiresAt: $data['expires_at'] ?? null,
            rawPayload: $data['_raw_payload'] ?? $payload,
        );
    }
}
