<?php

namespace Tests\Feature;

use App\Jobs\Importing\Connectors\RemotiveConnector;
use App\Models\JobSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RemotiveConnectorTest extends TestCase
{
    use RefreshDatabase;

    public function test_connector_maps_official_response_and_preserves_raw_payload(): void
    {
        config(['job_sources.remotive.limit' => 1]);
        Http::fake([
            'remotive.com/*' => Http::response(['jobs' => [$this->remoteJob(), [...$this->remoteJob(), 'id' => 2091106]]]),
        ]);
        $source = JobSource::factory()->create(['slug' => 'remotive']);

        $jobs = iterator_to_array(app(RemotiveConnector::class)->fetch($source));

        $this->assertCount(1, $jobs);
        $this->assertSame('2091105', $jobs[0]['external_id']);
        $this->assertSame('remote', $jobs[0]['workplace_type']);
        $this->assertSame('USA', $jobs[0]['location_text']);
        $this->assertSame(['android', 'AI/ML'], $jobs[0]['technologies']);
        $this->assertSame(2091105, $jobs[0]['_raw_payload']['id']);
        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://remotive.com/api/remote-jobs?limit=1');
    }

    public function test_manual_command_imports_remotive_jobs(): void
    {
        Http::fake([
            'remotive.com/*' => Http::response(['jobs' => [$this->remoteJob(), [...$this->remoteJob(), 'id' => 2091106]]]),
        ]);

        $this->artisan('jobs:sync', ['source' => 'remotive', '--limit' => 1])
            ->assertSuccessful();

        $this->assertDatabaseHas('job_sources', ['slug' => 'remotive', 'name' => 'Remotive']);
        $this->assertDatabaseHas('job_listings', [
            'external_id' => '2091105',
            'category' => 'All others',
            'location_text' => 'USA',
            'salary_text' => '$14/hour',
        ]);
        $this->assertDatabaseHas('job_import_runs', [
            'total_received' => 1,
            'created_count' => 1,
            'failed_count' => 0,
        ]);
    }

    public function test_manual_command_rejects_invalid_limit_without_requesting_api(): void
    {
        Http::fake();

        $this->artisan('jobs:sync', ['source' => 'remotive', '--limit' => 0])
            ->assertExitCode(2);

        Http::assertNothingSent();
    }

    /** @return array<string, mixed> */
    private function remoteJob(): array
    {
        return [
            'id' => 2091105,
            'url' => 'https://remotive.com/remote-jobs/all-others/content-reviewer-english-us-2091105',
            'title' => 'Content Reviewer - English US',
            'company_name' => 'TELUS Digital',
            'company_logo' => 'https://remotive.com/logo.png',
            'category' => 'All others',
            'job_type' => 'part_time',
            'publication_date' => '2026-08-21T05:54:39',
            'candidate_required_location' => 'USA',
            'salary' => '$14/hour',
            'tags' => ['android', 'AI/ML'],
            'description' => '<p>Review content.</p>',
        ];
    }
}
