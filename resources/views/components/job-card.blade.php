@props(['job'])

@php
    $workplaceLabels = ['remote' => 'Remoto', 'hybrid' => 'Híbrido', 'onsite' => 'Presencial'];
    $employmentLabels = ['full_time' => 'Tempo integral', 'part_time' => 'Meio período', 'contract' => 'Contrato', 'freelance' => 'Freelance', 'internship' => 'Estágio'];
    $companyName = $job->company?->name ?? 'Empresa não informada';
    $companyInitial = mb_strtoupper(mb_substr($companyName, 0, 1));
    $location = $job->location_text ?: collect([$job->city, $job->state, $job->country_code])->filter()->join(', ');
    $summary = \Illuminate\Support\Str::limit(trim(strip_tags(html_entity_decode($job->description ?? ''))), 180);
@endphp

<article class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md sm:p-6">
    <div class="flex items-start gap-4">
        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-lg font-bold text-indigo-700 ring-1 ring-indigo-100">
            {{ $companyInitial }}
        </div>
        <div class="min-w-0 flex-1">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-slate-500">{{ $companyName }}</p>
                    <h2 class="mt-1 text-lg font-bold leading-snug text-slate-900">
                        <a href="{{ route('jobs.show', $job) }}" class="rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 group-hover:text-indigo-700">
                            {{ $job->title }}
                        </a>
                    </h2>
                </div>
                <span class="inline-flex w-fit shrink-0 items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">
                    {{ $job->jobSource->name }}
                </span>
            </div>

            <div class="mt-3 flex flex-wrap gap-x-4 gap-y-2 text-sm text-slate-600">
                @if ($location)
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 21s6-5.1 6-11a6 6 0 1 0-12 0c0 5.9 6 11 6 11Z"/><circle cx="12" cy="10" r="2"/></svg>
                        {{ $location }}
                    </span>
                @endif
                @if ($job->salary_text)
                    <span class="inline-flex items-center gap-1.5 font-medium text-slate-700">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16v10H4zM8 12h.01M16 12h.01M12 9.5v5"/></svg>
                        {{ $job->salary_text }}
                    </span>
                @endif
                @if ($job->published_at)
                    <span>Publicada {{ $job->published_at->diffForHumans() }}</span>
                @endif
            </div>

            @if ($summary)
                <p class="mt-4 text-sm leading-6 text-slate-600">{{ $summary }}</p>
            @endif

            <div class="mt-4 flex flex-wrap items-center gap-2">
                @if ($job->workplace_type)
                    <span class="rounded-md bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">{{ $workplaceLabels[$job->workplace_type] ?? $job->workplace_type }}</span>
                @endif
                @if ($job->employment_type)
                    <span class="rounded-md bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">{{ $employmentLabels[$job->employment_type] ?? $job->employment_type }}</span>
                @endif
                @foreach ($job->technologies->take(4) as $technology)
                    <span class="rounded-md bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">{{ $technology->name }}</span>
                @endforeach
                @if ($job->technologies->count() > 4)
                    <span class="text-xs font-medium text-slate-500">+{{ $job->technologies->count() - 4 }}</span>
                @endif
            </div>
        </div>
    </div>
</article>