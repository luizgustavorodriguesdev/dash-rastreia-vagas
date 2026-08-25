<?php

namespace Tests\Feature;

use App\Jobs\Importing\Actions\ImportJobsFromSource;
use App\Jobs\Importing\Contracts\JobSourceConnector;
use App\Jobs\RunJobSourceImport;
use App\Models\JobSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RunJobSourceImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_resolves_connector_and_imports_active_source(): void
    {
        $source = JobSource::factory()->create();
        $connector = $this->connector();
        $this->app->instance('test.job-source-connector', $connector);

        $job = new RunJobSourceImport($source->id, 'test.job-source-connector');
        $job->handle(app(ImportJobsFromSource::class));

        $this->assertSame(1, $connector->calls);
        $this->assertDatabaseHas('job_listings', ['external_id' => 'queued-123']);
        $this->assertDatabaseCount('job_import_runs', 1);
        $this->assertSame((string) $source->id, $job->uniqueId());
        $this->assertSame([30, 120], $job->backoff());
    }

    public function test_job_skips_inactive_source(): void
    {
        $source = JobSource::factory()->create(['is_active' => false]);
        $connector = $this->connector();
        $this->app->instance('test.job-source-connector', $connector);

        (new RunJobSourceImport($source->id, 'test.job-source-connector'))
            ->handle(app(ImportJobsFromSource::class));

        $this->assertSame(0, $connector->calls);
        $this->assertDatabaseCount('job_listings', 0);
        $this->assertDatabaseCount('job_import_runs', 0);
    }

    private function connector(): JobSourceConnector
    {
        return new class implements JobSourceConnector
        {
            public int $calls = 0;

            public function fetch(JobSource $source): iterable
            {
                $this->calls++;

                yield [
                    'external_id' => 'queued-123',
                    'title' => 'Vaga importada pela fila',
                    'url' => 'https://jobs.example.com/queued-123',
                    'technologies' => ['PHP'],
                ];
            }
        };
    }
}
