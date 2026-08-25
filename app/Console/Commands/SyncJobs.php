<?php

namespace App\Console\Commands;

use App\Jobs\Importing\Actions\ImportJobsFromSource;
use App\Jobs\Importing\Contracts\JobSourceConnector;
use App\Models\JobImportRun;
use App\Models\JobSource;
use Illuminate\Console\Command;
use Throwable;

class SyncJobs extends Command
{
    protected $signature = 'jobs:sync
                            {source=remotive : Identificador da fonte configurada}
                            {--limit=50 : Quantidade maxima de vagas por consulta}';

    protected $description = 'Sincroniza manualmente as vagas de uma fonte configurada';

    public function handle(ImportJobsFromSource $importer): int
    {
        $slug = (string) $this->argument('source');
        $configuration = config("job_sources.connectors.{$slug}");

        if (! is_array($configuration)) {
            $this->components->error("Fonte [{$slug}] nao configurada.");

            return self::INVALID;
        }

        $limit = filter_var($this->option('limit'), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 100],
        ]);

        if ($limit === false) {
            $this->components->error('O limite deve ser um numero inteiro entre 1 e 100.');

            return self::INVALID;
        }

        config(["job_sources.{$slug}.limit" => $limit]);

        $source = JobSource::query()->firstOrCreate(
            ['slug' => $slug],
            ['name' => $configuration['name'], 'base_url' => $configuration['base_url'], 'is_active' => true],
        );

        if (! $source->is_active) {
            $this->components->error("A fonte [{$source->name}] esta inativa.");

            return self::FAILURE;
        }

        $connector = app($configuration['connector']);

        if (! $connector instanceof JobSourceConnector) {
            $this->components->error('O conector configurado e invalido.');

            return self::FAILURE;
        }

        $this->components->info("Sincronizando {$source->name} manualmente...");

        try {
            $run = $importer->execute($source, $connector);
        } catch (Throwable $exception) {
            report($exception);
            $this->components->error('A sincronizacao falhou: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->table(
            ['Recebidas', 'Criadas', 'Atualizadas', 'Inalteradas', 'Invalidas'],
            [[
                $run->total_received,
                $run->created_count,
                $run->updated_count,
                $run->unchanged_count,
                $run->failed_count,
            ]],
        );

        if ($run->status === JobImportRun::STATUS_COMPLETED_WITH_ERRORS) {
            $this->components->warn('Sincronizacao concluida com itens invalidos. Consulte job_import_runs.');

            return self::FAILURE;
        }

        $this->components->info('Sincronizacao concluida.');

        return self::SUCCESS;
    }
}
