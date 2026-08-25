<?php

namespace App\Jobs\Importing\Contracts;

use App\Models\JobSource;

interface JobSourceConnector
{
    /** @return iterable<array<string, mixed>> */
    public function fetch(JobSource $source): iterable;
}
