<x-app-layout>
    <x-slot name="title">{{ $jobListing->title }}</x-slot>

    @php
        $workplaceLabels = ['remote' => 'Remoto', 'hybrid' => 'Híbrido', 'onsite' => 'Presencial'];
        $employmentLabels = ['full_time' => 'Tempo integral', 'part_time' => 'Meio período', 'contract' => 'Contrato', 'freelance' => 'Freelance', 'internship' => 'Estágio'];
        $companyName = $jobListing->company?->name ?? 'Empresa não informada';
        $location = $jobListing->location_text ?: collect([$jobListing->city, $jobListing->state, $jobListing->country_code])->filter()->join(', ');
        $html = html_entity_decode($jobListing->description ?? '');
        $description = trim(strip_tags(str_ireplace(['</p>', '</div>', '</li>', '<br>', '<br/>', '<br />'], "\n", $html)));
    @endphp

    <div class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-2 text-sm text-slate-500" aria-label="Navegação estrutural">
                <a href="{{ route('dashboard') }}" class="font-medium hover:text-indigo-700">Vagas</a>
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd"/></svg>
                <span class="truncate text-slate-700" aria-current="page">{{ $jobListing->title }}</span>
            </nav>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <header class="border-b border-slate-200 p-6 sm:p-8">
                        <div class="flex items-start gap-4">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-xl font-bold text-indigo-700 ring-1 ring-indigo-100">
                                {{ mb_strtoupper(mb_substr($companyName, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="font-semibold text-indigo-700">{{ $companyName }}</p>
                                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">{{ $jobListing->title }}</h1>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @if ($jobListing->workplace_type)
                                        <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">{{ $workplaceLabels[$jobListing->workplace_type] ?? $jobListing->workplace_type }}</span>
                                    @endif
                                    @if ($jobListing->employment_type)
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $employmentLabels[$jobListing->employment_type] ?? $jobListing->employment_type }}</span>
                                    @endif
                                    @if ($jobListing->category)
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $jobListing->category }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </header>

                    <div class="p-6 sm:p-8">
                        <h2 class="text-lg font-bold text-slate-900">Sobre a vaga</h2>
                        @if ($description)
                            <div class="mt-4 whitespace-pre-line text-sm leading-7 text-slate-700">{{ $description }}</div>
                        @else
                            <p class="mt-4 text-sm text-slate-600">A fonte não forneceu uma descrição detalhada.</p>
                        @endif

                        @if ($jobListing->technologies->isNotEmpty())
                            <div class="mt-8 border-t border-slate-100 pt-6">
                                <h2 class="text-lg font-bold text-slate-900">Tecnologias e competências</h2>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($jobListing->technologies as $technology)
                                        <span class="rounded-lg bg-indigo-50 px-3 py-1.5 text-sm font-medium text-indigo-700 ring-1 ring-inset ring-indigo-100">{{ $technology->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </article>
            </div>

            <aside class="space-y-5">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:sticky lg:top-24">
                    <a href="{{ $jobListing->url }}" target="_blank" rel="noopener noreferrer" class="flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Ver vaga na fonte
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5h5v5M10 14 19 5M19 14v4a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h4"/></svg>
                    </a>
                    <p class="mt-3 text-center text-xs leading-5 text-slate-500">Você será direcionado para {{ $jobListing->jobSource->name }}.</p>

                    <dl class="mt-6 space-y-4 border-t border-slate-100 pt-5 text-sm">
                        @if ($location)
                            <div><dt class="font-medium text-slate-500">Localização</dt><dd class="mt-1 font-semibold text-slate-800">{{ $location }}</dd></div>
                        @endif
                        @if ($jobListing->salary_text)
                            <div><dt class="font-medium text-slate-500">Remuneração</dt><dd class="mt-1 font-semibold text-slate-800">{{ $jobListing->salary_text }}</dd></div>
                        @endif
                        @if ($jobListing->published_at)
                            <div><dt class="font-medium text-slate-500">Publicação</dt><dd class="mt-1 font-semibold text-slate-800">{{ $jobListing->published_at->format('d/m/Y') }}</dd></div>
                        @endif
                        <div><dt class="font-medium text-slate-500">Fonte</dt><dd class="mt-1 font-semibold text-slate-800">{{ $jobListing->jobSource->name }}</dd></div>
                    </dl>
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>