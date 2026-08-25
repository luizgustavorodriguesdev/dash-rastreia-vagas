<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\JobListing;
use App\Models\JobSource;
use App\Models\Technology;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobListingDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_external_id_is_unique_within_the_same_source(): void
    {
        $source = JobSource::factory()->create();

        JobListing::factory()->for($source)->create(['external_id' => 'vaga-123']);

        $this->expectException(QueryException::class);

        JobListing::factory()->for($source)->create(['external_id' => 'vaga-123']);
    }

    public function test_same_external_id_is_allowed_for_different_sources(): void
    {
        JobListing::factory()->create(['external_id' => 'vaga-123']);
        JobListing::factory()->create(['external_id' => 'vaga-123']);

        $this->assertDatabaseCount('job_listings', 2);
    }

    public function test_listing_preserves_source_company_and_technologies_relationships(): void
    {
        $source = JobSource::factory()->create();
        $company = Company::factory()->create();
        $technologies = Technology::factory()->count(2)->create();
        $listing = JobListing::factory()->for($source)->for($company)->create();

        $listing->technologies()->attach($technologies);

        $this->assertTrue($listing->jobSource->is($source));
        $this->assertTrue($listing->company->is($company));
        $this->assertCount(2, $listing->technologies);
        $this->assertDatabaseCount('job_listing_technology', 2);
    }

    public function test_deleting_company_keeps_listing_and_clears_company_reference(): void
    {
        $company = Company::factory()->create();
        $listing = JobListing::factory()->for($company)->create();

        $company->delete();

        $this->assertDatabaseHas('job_listings', [
            'id' => $listing->id,
            'company_id' => null,
        ]);
    }

    public function test_payload_dates_and_salary_are_cast_to_domain_types(): void
    {
        $listing = JobListing::factory()->create([
            'raw_payload' => ['provider' => ['score' => 95]],
            'salary_min' => 7500.50,
        ]);

        $this->assertSame(95, $listing->raw_payload['provider']['score']);
        $this->assertSame('7500.50', $listing->salary_min);
        $this->assertNotNull($listing->published_at);
        $this->assertNotNull($listing->imported_at);
    }

    public function test_source_with_listings_cannot_be_deleted(): void
    {
        $source = JobSource::factory()->create();
        JobListing::factory()->for($source)->create();

        $this->expectException(QueryException::class);

        $source->delete();
    }
}
