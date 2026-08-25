<?php

namespace App\Models;

use Database\Factories\JobSourceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobSource extends Model
{
    /** @use HasFactory<JobSourceFactory> */
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'base_url', 'is_active', 'last_synced_at', 'last_sync_error',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'last_synced_at' => 'datetime'];
    }

    public function jobListings(): HasMany
    {
        return $this->hasMany(JobListing::class);
    }
}
