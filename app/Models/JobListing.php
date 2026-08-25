<?php

namespace App\Models;

use Database\Factories\JobListingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class JobListing extends Model
{
    /** @use HasFactory<JobListingFactory> */
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'job_source_id', 'company_id', 'external_id', 'title', 'description',
        'url', 'employment_type', 'workplace_type', 'seniority', 'city', 'state',
        'country_code', 'salary_min', 'salary_max', 'salary_currency',
        'salary_period', 'status', 'content_hash', 'raw_payload', 'published_at',
        'expires_at', 'imported_at',
    ];

    protected function casts(): array
    {
        return [
            'salary_min' => 'decimal:2',
            'salary_max' => 'decimal:2',
            'raw_payload' => 'array',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'imported_at' => 'datetime',
        ];
    }

    public function jobSource(): BelongsTo
    {
        return $this->belongsTo(JobSource::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function technologies(): BelongsToMany
    {
        return $this->belongsToMany(Technology::class)->withTimestamps();
    }
}
