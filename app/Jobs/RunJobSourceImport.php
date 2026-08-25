<?php

namespace App\Jobs;

use App\Jobs\Importing\Actions\ImportJobsFromSource;
use App\Jobs\Importing\Contracts\JobSourceConnector;
use App\Models\JobSource;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use RuntimeException;

class RunJobSourceImport implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public int $uniqueFor = 300;

    /** @param class-string<JobSourceConnector> $connectorClass */
    public function __construct(
        public readonly int $sourceId,
        public readonly string $connectorClass,
    ) {}

    /** @return list<int> */
    public function backoff(): array
    {
        return [30, 120];
    }

    public function uniqueId(): string
    {
        return (string) $this->sourceId;
    }

    public function handle(ImportJobsFromSource $importer): void
    {
        $source = JobSource::query()->findOrFail($this->sourceId);

        if (! $source->is_active) {
            return;
        }

        $connector = app($this->connectorClass);

        if (! $connector instanceof JobSourceConnector) {
            throw new RuntimeException('O conector deve implementar JobSourceConnector.');
        }

        $importer->execute($source, $connector);
    }
}
