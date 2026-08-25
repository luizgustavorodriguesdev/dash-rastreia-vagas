<?php

namespace App\Models;

use Database\Factories\TechnologyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Technology extends Model
{
    /** @use HasFactory<TechnologyFactory> */
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    public function jobListings(): BelongsToMany
    {
        return $this->belongsToMany(JobListing::class)->withTimestamps();
    }
}
