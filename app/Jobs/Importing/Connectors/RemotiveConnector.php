<?php

namespace App\Jobs\Importing\Connectors;

use App\Jobs\Importing\Contracts\JobSourceConnector;
use App\Models\JobSource;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class RemotiveConnector implements JobSourceConnector
{
    public function fetch(JobSource $source): iterable
    {
        $limit = max(1, min(100, (int) config('job_sources.remotive.limit', 50)));
        $response = $this->client()->get(config('job_sources.remotive.endpoint'), [
            'limit' => $limit,
        ])->throw();
        $jobs = $response->json('jobs');

        if (! is_array($jobs)) {
            throw new RuntimeException('A API Remotive retornou um formato inesperado.');
        }

        foreach (array_slice($jobs, 0, $limit) as $job) {
            if (! is_array($job)) {
                continue;
            }

            yield $this->map($job);
        }
    }

    private function client(): PendingRequest
    {
        return Http::acceptJson()
            ->withUserAgent('DashRastreiaVagas/1.0 (+'.config('app.url').')')
            ->timeout(30)
            ->retry(2, 1000);
    }

    /**
     * @param  array<string, mixed>  $job
     * @return array<string, mixed>
     */
    private function map(array $job): array
    {
        return [
            'external_id' => (string) ($job['id'] ?? ''),
            'title' => $job['title'] ?? null,
            'url' => $job['url'] ?? null,
            'category' => $job['category'] ?? null,
            'description' => $job['description'] ?? null,
            'company' => isset($job['company_name']) ? [
                'name' => $job['company_name'],
                'logo_url' => $job['company_logo'] ?? null,
                'location' => $job['candidate_required_location'] ?? null,
            ] : null,
            'technologies' => array_values(array_filter($job['tags'] ?? [], 'is_string')),
            'employment_type' => $job['job_type'] ?? null,
            'workplace_type' => 'remote',
            'location_text' => $job['candidate_required_location'] ?? null,
            'salary_text' => $job['salary'] ?? null,
            'published_at' => $job['publication_date'] ?? null,
            '_raw_payload' => $job,
        ];
    }
}
