<?php

namespace App\Jobs\Importing\Actions;

use App\Jobs\Importing\Contracts\JobSourceConnector;
use App\Jobs\Importing\Data\ExternalJobData;
use App\Models\Company;
use App\Models\JobImportRun;
use App\Models\JobListing;
use App\Models\JobSource;
use App\Models\Technology;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class ImportJobsFromSource
{
    public function execute(JobSource $source, JobSourceConnector $connector): JobImportRun
    {
        $run = $source->importRuns()->create([
            'connector' => $connector::class,
            'status' => JobImportRun::STATUS_RUNNING,
            'started_at' => now(),
        ]);

        $counters = [
            'total_received' => 0,
            'created_count' => 0,
            'updated_count' => 0,
            'unchanged_count' => 0,
            'failed_count' => 0,
        ];
        $errors = [];

        try {
            foreach ($connector->fetch($source) as $payload) {
                $counters['total_received']++;

                try {
                    $data = ExternalJobData::fromArray($payload);
                    $outcome = DB::transaction(fn (): string => $this->persist($source, $data));
                    $counters[$outcome.'_count']++;
                } catch (Throwable $exception) {
                    $counters['failed_count']++;
                    $errors[] = [
                        'external_id' => is_array($payload) ? ($payload['external_id'] ?? null) : null,
                        'message' => Str::limit($exception->getMessage(), 500),
                    ];
                }
            }

            $status = $counters['failed_count'] > 0
                ? JobImportRun::STATUS_COMPLETED_WITH_ERRORS
                : JobImportRun::STATUS_COMPLETED;

            $run->update([
                ...$counters,
                'status' => $status,
                'errors' => $errors ?: null,
                'finished_at' => now(),
            ]);

            $source->update([
                'last_synced_at' => now(),
                'last_sync_error' => $errors ? $counters['failed_count'].' item(ns) nao importado(s).' : null,
            ]);

            return $run->fresh();
        } catch (Throwable $exception) {
            $run->update([
                ...$counters,
                'status' => JobImportRun::STATUS_FAILED,
                'errors' => [[
                    'external_id' => null,
                    'message' => Str::limit($exception->getMessage(), 500),
                ]],
                'finished_at' => now(),
            ]);

            $source->update(['last_sync_error' => Str::limit($exception->getMessage(), 500)]);

            throw $exception;
        }
    }

    private function persist(JobSource $source, ExternalJobData $data): string
    {
        $company = $this->persistCompany($data->company);
        $technologies = $this->persistTechnologies($data->technologies);
        $attributes = [
            'company_id' => $company?->id,
            'title' => $data->title,
            'category' => $data->category,
            'description' => $data->description,
            'url' => $data->url,
            'employment_type' => $data->employmentType,
            'workplace_type' => $data->workplaceType,
            'seniority' => $data->seniority,
            'city' => $data->city,
            'state' => $data->state,
            'country_code' => $data->countryCode,
            'location_text' => $data->locationText,
            'salary_min' => $data->salaryMin,
            'salary_max' => $data->salaryMax,
            'salary_currency' => $data->salaryCurrency,
            'salary_period' => $data->salaryPeriod,
            'salary_text' => $data->salaryText,
            'status' => JobListing::STATUS_ACTIVE,
            'published_at' => $data->publishedAt,
            'expires_at' => $data->expiresAt,
        ];
        $hashData = [...$attributes, 'technologies' => $technologies->pluck('slug')->sort()->values()->all()];
        $contentHash = hash('sha256', json_encode($hashData, JSON_THROW_ON_ERROR));

        $listing = JobListing::query()
            ->whereBelongsTo($source)
            ->where('external_id', $data->externalId)
            ->first();

        if ($listing === null) {
            $listing = $source->jobListings()->create([
                ...$attributes,
                'external_id' => $data->externalId,
                'content_hash' => $contentHash,
                'raw_payload' => $data->rawPayload,
                'imported_at' => now(),
            ]);
            $outcome = 'created';
        } elseif ($listing->content_hash !== $contentHash) {
            $listing->update([
                ...$attributes,
                'content_hash' => $contentHash,
                'raw_payload' => $data->rawPayload,
                'imported_at' => now(),
            ]);
            $outcome = 'updated';
        } else {
            $listing->update(['raw_payload' => $data->rawPayload, 'imported_at' => now()]);
            $outcome = 'unchanged';
        }

        $listing->technologies()->sync($technologies->pluck('id')->all());

        return $outcome;
    }

    /** @param array{name: string, website_url?: ?string, logo_url?: ?string, location?: ?string}|null $data */
    private function persistCompany(?array $data): ?Company
    {
        if ($data === null) {
            return null;
        }

        $slug = Str::slug($data['name']);
        $company = Company::query()->firstOrCreate(
            ['slug' => $slug],
            ['name' => $data['name']],
        );
        $company->fill(array_filter([
            'website_url' => $data['website_url'] ?? null,
            'logo_url' => $data['logo_url'] ?? null,
            'location' => $data['location'] ?? null,
        ], fn ($value): bool => $value !== null));

        if ($company->isDirty()) {
            $company->save();
        }

        return $company;
    }

    /** @param list<string> $names */
    private function persistTechnologies(array $names)
    {
        return collect($names)
            ->map(fn (string $name): string => trim($name))
            ->filter()
            ->unique(fn (string $name): string => Str::lower($name))
            ->map(function (string $name): Technology {
                $slug = Str::slug($name);

                return Technology::query()->firstOrCreate(['slug' => $slug], ['name' => $name]);
            })
            ->values();
    }
}
