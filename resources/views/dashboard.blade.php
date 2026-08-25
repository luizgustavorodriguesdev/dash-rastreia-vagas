<x-app-layout>
    <x-slot name="title">Vagas</x-slot>

    <section class="overflow-hidden bg-slate-950 text-white">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
            <div class="max-w-3xl">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-indigo-200 ring-1 ring-white/15">
                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                    Oportunidades atualizadas
                </span>
                <h1 class="mt-5 text-3xl font-bold tracking-tight sm:text-4xl">Encontre sua próxima oportunidade</h1>
                <p class="mt-3 max-w-2xl text-base leading-7 text-slate-300">Vagas reunidas de fontes confiáveis, organizadas para você comparar tecnologias, modalidade e localização sem ruído.</p>
            </div>

            <dl class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach ([['label' => 'Vagas ativas', 'value' => $stats['active']], ['label' => 'Remotas', 'value' => $stats['remote']], ['label' => 'Últimos 7 dias', 'value' => $stats['recent']], ['label' => 'Fontes ativas', 'value' => $stats['sources']]] as $stat)
                    <div class="rounded-xl border border-white/10 bg-white/5 px-4 py-3 backdrop-blur">
                        <dt class="text-xs font-medium text-slate-400">{{ $stat['label'] }}</dt>
                        <dd class="mt-1 text-2xl font-bold text-white">{{ number_format($stat['value'], 0, ',', '.') }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="GET" action="{{ route('dashboard') }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5" aria-label="Filtros de vagas">
            <div class="grid gap-4 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <label for="q" class="mb-1.5 block text-sm font-semibold text-slate-700">Buscar</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7" stroke-width="1.8"/><path stroke-linecap="round" stroke-width="1.8" d="m20 20-3.5-3.5"/></svg>
                        <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" type="search" placeholder="Cargo, empresa ou tecnologia" class="block w-full rounded-lg border-slate-300 py-2.5 pl-10 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="sm:col-span-1 lg:col-span-2">
                    <label for="source" class="mb-1.5 block text-sm font-semibold text-slate-700">Fonte</label>
                    <select id="source" name="source" class="block w-full rounded-lg border-slate-300 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Todas</option>
                        @foreach ($sources as $source)
                            <option value="{{ $source->slug }}" @selected(($filters['source'] ?? '') === $source->slug)>{{ $source->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-1 lg:col-span-2">
                    <label for="workplace_type" class="mb-1.5 block text-sm font-semibold text-slate-700">Modalidade</label>
                    <select id="workplace_type" name="workplace_type" class="block w-full rounded-lg border-slate-300 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Todas</option>
                        <option value="remote" @selected(($filters['workplace_type'] ?? '') === 'remote')>Remoto</option>
                        <option value="hybrid" @selected(($filters['workplace_type'] ?? '') === 'hybrid')>Híbrido</option>
                        <option value="onsite" @selected(($filters['workplace_type'] ?? '') === 'onsite')>Presencial</option>
                    </select>
                </div>
                <div class="sm:col-span-1 lg:col-span-2">
                    <label for="employment_type" class="mb-1.5 block text-sm font-semibold text-slate-700">Contrato</label>
                    <select id="employment_type" name="employment_type" class="block w-full rounded-lg border-slate-300 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Todos</option>
                        <option value="full_time" @selected(($filters['employment_type'] ?? '') === 'full_time')>Tempo integral</option>
                        <option value="part_time" @selected(($filters['employment_type'] ?? '') === 'part_time')>Meio período</option>
                        <option value="contract" @selected(($filters['employment_type'] ?? '') === 'contract')>Contrato</option>
                        <option value="freelance" @selected(($filters['employment_type'] ?? '') === 'freelance')>Freelance</option>
                        <option value="internship" @selected(($filters['employment_type'] ?? '') === 'internship')>Estágio</option>
                    </select>
                </div>
                <div class="sm:col-span-1 lg:col-span-2">
                    <label for="category" class="mb-1.5 block text-sm font-semibold text-slate-700">Categoria</label>
                    <select id="category" name="category" class="block w-full rounded-lg border-slate-300 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Todas</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap items-center justify-end gap-3 border-t border-slate-100 pt-4">
                @if (collect($filters)->filter(fn ($value) => filled($value))->isNotEmpty())
                    <a href="{{ route('dashboard') }}" class="rounded-lg px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">Limpar filtros</a>
                @endif
                <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Aplicar filtros</button>
            </div>
        </form>

        <div class="mt-8 flex items-end justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-indigo-600">Resultados</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ $jobs->total() }} {{ $jobs->total() === 1 ? 'vaga encontrada' : 'vagas encontradas' }}</h2>
            </div>
            @if ($jobs->total() > 0)
                <p class="hidden text-sm text-slate-500 sm:block">Ordenadas pelas mais recentes</p>
            @endif
        </div>

        @if ($jobs->isEmpty())
            <div class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7" stroke-width="1.8"/><path stroke-linecap="round" stroke-width="1.8" d="m20 20-3.5-3.5"/></svg>
                </div>
                <h3 class="mt-4 text-lg font-bold text-slate-900">Nenhuma vaga encontrada</h3>
                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-600">Tente remover alguns filtros ou buscar por termos mais amplos.</p>
                <a href="{{ route('dashboard') }}" class="mt-5 inline-flex rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Ver todas as vagas</a>
            </div>
        @else
            <div class="mt-6 space-y-4">
                @foreach ($jobs as $job)
                    <x-job-card :job="$job" />
                @endforeach
            </div>
            <div class="mt-8">{{ $jobs->links() }}</div>
        @endif
    </div>
</x-app-layout>