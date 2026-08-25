<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobListingFilterRequest;
use App\Models\JobListing;
use App\Models\JobSource;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class JobListingController extends Controller
{
    public function index(JobListingFilterRequest $request): View
    {
        $filters = $request->validated();
        $search = trim($filters['q'] ?? '');

        $jobs = JobListing::query()
            ->with(['company', 'jobSource', 'technologies'])
            ->where('status', JobListing::STATUS_ACTIVE)
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('company', fn (Builder $company): Builder => $company->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('technologies', fn (Builder $technology): Builder => $technology->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['source'] ?? null, fn (Builder $query, string $source): Builder => $query->whereHas(
                'jobSource',
                fn (Builder $jobSource): Builder => $jobSource->where('slug', $source),
            ))
            ->when($filters['workplace_type'] ?? null, fn (Builder $query, string $type): Builder => $query->where('workplace_type', $type))
            ->when($filters['employment_type'] ?? null, fn (Builder $query, string $type): Builder => $query->where('employment_type', $type))
            ->when($filters['category'] ?? null, fn (Builder $query, string $category): Builder => $query->where('category', $category))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $activeJobs = JobListing::query()->where('status', JobListing::STATUS_ACTIVE);
        $stats = [
            'active' => (clone $activeJobs)->count(),
            'remote' => (clone $activeJobs)->where('workplace_type', 'remote')->count(),
            'recent' => (clone $activeJobs)->where('published_at', '>=', now()->subDays(7))->count(),
            'sources' => JobSource::query()->where('is_active', true)->has('jobListings')->count(),
        ];

        $sources = JobSource::query()
            ->where('is_active', true)
            ->has('jobListings')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
        $categories = JobListing::query()
            ->where('status', JobListing::STATUS_ACTIVE)
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('dashboard', compact('jobs', 'stats', 'sources', 'categories', 'filters'));
    }

    public function show(JobListing $jobListing): View
    {
        $jobListing->load(['company', 'jobSource', 'technologies']);

        return view('jobs.show', compact('jobListing'));
    }
}
