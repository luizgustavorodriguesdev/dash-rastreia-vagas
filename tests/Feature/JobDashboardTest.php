<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\JobListing;
use App\Models\JobSource;
use App\Models\Technology;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_dashboard_lists_only_active_jobs(): void
    {
        $user = User::factory()->create();
        JobListing::factory()->create(['title' => 'Vaga Laravel ativa']);
        JobListing::factory()->create(['title' => 'Vaga encerrada', 'status' => JobListing::STATUS_CLOSED]);

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertSee('Vaga Laravel ativa')
            ->assertDontSee('Vaga encerrada');
    }

    public function test_filters_are_combined_and_kept_in_the_form(): void
    {
        $user = User::factory()->create();
        $source = JobSource::factory()->create(['slug' => 'remotive', 'name' => 'Remotive']);
        JobListing::factory()->for($source)->create([
            'title' => 'Desenvolvedor PHP remoto',
            'workplace_type' => 'remote',
            'employment_type' => 'full_time',
            'category' => 'Software Development',
        ]);
        JobListing::factory()->create(['title' => 'Designer presencial', 'workplace_type' => 'onsite']);

        $response = $this->actingAs($user)->get('/dashboard?'.http_build_query([
            'q' => 'PHP',
            'source' => 'remotive',
            'workplace_type' => 'remote',
            'employment_type' => 'full_time',
            'category' => 'Software Development',
        ]));

        $response->assertOk()
            ->assertSee('Desenvolvedor PHP remoto')
            ->assertDontSee('Designer presencial')
            ->assertSee('value="PHP"', false)
            ->assertSee('value="remotive" selected', false);
    }

    public function test_search_matches_company_and_technology(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['name' => 'Acme Tecnologia']);
        $technology = Technology::factory()->create(['name' => 'Kubernetes', 'slug' => 'kubernetes']);
        $job = JobListing::factory()->for($company)->create(['title' => 'Pessoa Engenheira de Plataforma']);
        $job->technologies()->attach($technology);

        $this->actingAs($user)->get('/dashboard?q=Kubernetes')
            ->assertOk()
            ->assertSee('Pessoa Engenheira de Plataforma');

        $this->actingAs($user)->get('/dashboard?q=Acme')
            ->assertOk()
            ->assertSee('Pessoa Engenheira de Plataforma');
    }

    public function test_pagination_preserves_query_string(): void
    {
        $user = User::factory()->create();
        JobListing::factory()->count(12)->create(['title' => 'Laravel Developer']);

        $this->actingAs($user)->get('/dashboard?q=Laravel')
            ->assertOk()
            ->assertSee('page=2')
            ->assertSee('q=Laravel');
    }

    public function test_detail_page_escapes_external_description_and_attributes_source(): void
    {
        $user = User::factory()->create();
        $source = JobSource::factory()->create(['name' => 'Remotive']);
        $job = JobListing::factory()->for($source)->create([
            'title' => 'Vaga segura',
            'description' => '<p>Descrição legítima</p><script>alert("xss")</script>',
            'url' => 'https://remotive.com/remote-jobs/teste',
        ]);

        $this->actingAs($user)->get(route('jobs.show', $job))
            ->assertOk()
            ->assertSee('Descrição legítima')
            ->assertSee('Remotive')
            ->assertSee('https://remotive.com/remote-jobs/teste', false)
            ->assertDontSee('<script>', false);
    }

    public function test_invalid_filter_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard?workplace_type=qualquer')
            ->assertRedirect()
            ->assertSessionHasErrors('workplace_type');
    }
}
