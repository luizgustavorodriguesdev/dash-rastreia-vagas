<?php

namespace Tests\Feature;

use App\Jobs\Importing\Actions\ImportJobsFromSource;
use App\Jobs\Importing\Contracts\JobSourceConnector;
use App\Models\JobImportRun;
use App\Models\JobSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class JobImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_a_normalized_job_and_records_the_run(): void
    {
        $source = JobSource::factory()->create();

        $run = app(ImportJobsFromSource::class)->execute($source, $this->connector([
            $this->jobPayload(),
        ]));

        $this->assertSame(JobImportRun::STATUS_COMPLETED, $run->status);
        $this->assertSame(1, $run->total_received);
        $this->assertSame(1, $run->created_count);
        $this->assertSame(0, $run->failed_count);
        $this->assertDatabaseHas('job_listings', [
            'job_source_id' => $source->id,
            'external_id' => 'remote-123',
            'title' => 'Pessoa Desenvolvedora Laravel',
            'country_code' => 'BR',
        ]);
        $this->assertDatabaseHas('companies', ['slug' => 'empresa-exemplo']);
        $this->assertDatabaseHas('technologies', ['slug' => 'laravel']);
        $this->assertDatabaseCount('job_listing_technology', 2);
        $this->assertNotNull($source->fresh()->last_synced_at);
    }

    public function test_reimport_is_idempotent_and_only_counts_real_content_changes(): void
    {
        $source = JobSource::factory()->create();
        $importer = app(ImportJobsFromSource::class);
        $payload = $this->jobPayload();

        $firstRun = $importer->execute($source, $this->connector([$payload]));
        $secondRun = $importer->execute($source, $this->connector([$payload]));
        $payload['title'] = 'Pessoa Desenvolvedora Laravel Senior';
        $thirdRun = $importer->execute($source, $this->connector([$payload]));

        $this->assertSame(1, $firstRun->created_count);
        $this->assertSame(1, $secondRun->unchanged_count);
        $this->assertSame(1, $thirdRun->updated_count);
        $this->assertDatabaseCount('job_listings', 1);
        $this->assertDatabaseHas('job_listings', ['title' => 'Pessoa Desenvolvedora Laravel Senior']);
    }

    public function test_invalid_item_does_not_prevent_other_items_from_being_imported(): void
    {
        $source = JobSource::factory()->create();
        $invalid = $this->jobPayload();
        $invalid['external_id'] = 'invalid-1';
        $invalid['url'] = 'nao-e-uma-url';

        $run = app(ImportJobsFromSource::class)->execute(
            $source,
            $this->connector([$invalid, $this->jobPayload()]),
        );

        $this->assertSame(JobImportRun::STATUS_COMPLETED_WITH_ERRORS, $run->status);
        $this->assertSame(2, $run->total_received);
        $this->assertSame(1, $run->created_count);
        $this->assertSame(1, $run->failed_count);
        $this->assertSame('invalid-1', $run->errors[0]['external_id']);
        $this->assertDatabaseCount('job_listings', 1);
        $this->assertNotNull($source->fresh()->last_sync_error);
    }

    public function test_connector_failure_is_audited_and_propagated(): void
    {
        $source = JobSource::factory()->create();
        $connector = new class implements JobSourceConnector
        {
            public function fetch(JobSource $source): iterable
            {
                throw new RuntimeException('Fonte temporariamente indisponivel.');
            }
        };

        try {
            app(ImportJobsFromSource::class)->execute($source, $connector);
            $this->fail('A falha do conector deveria ser propagada.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Fonte temporariamente indisponivel.', $exception->getMessage());
        }

        $this->assertDatabaseHas('job_import_runs', [
            'job_source_id' => $source->id,
            'status' => JobImportRun::STATUS_FAILED,
        ]);
        $this->assertSame('Fonte temporariamente indisponivel.', $source->fresh()->last_sync_error);
    }

    /** @param list<array<string, mixed>> $items */
    private function connector(array $items): JobSourceConnector
    {
        return new class($items) implements JobSourceConnector
        {
            /** @param list<array<string, mixed>> $items */
            public function __construct(private readonly array $items) {}

            public function fetch(JobSource $source): iterable
            {
                yield from $this->items;
            }
        };
    }

    /** @return array<string, mixed> */
    private function jobPayload(): array
    {
        return [
            'external_id' => 'remote-123',
            'title' => 'Pessoa Desenvolvedora Laravel',
            'description' => 'Desenvolvimento e manutencao de aplicacoes web.',
            'url' => 'https://jobs.example.com/vagas/remote-123',
            'company' => [
                'name' => 'Empresa Exemplo',
                'website_url' => 'https://example.com',
                'location' => 'Sao Paulo, SP',
            ],
            'technologies' => ['Laravel', 'PHP'],
            'employment_type' => 'full_time',
            'workplace_type' => 'remote',
            'seniority' => 'mid',
            'city' => 'Sao Paulo',
            'state' => 'SP',
            'country_code' => 'br',
            'salary_min' => 7000,
            'salary_max' => 10000,
            'salary_currency' => 'brl',
            'salary_period' => 'month',
            'published_at' => '2026-08-24 10:00:00',
            'expires_at' => '2026-09-24 10:00:00',
        ];
    }
}
