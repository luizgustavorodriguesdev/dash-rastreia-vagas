<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_source_id')->constrained()->restrictOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('external_id');
            $table->string('title');
            $table->longText('description')->nullable();
            $table->text('url');
            $table->string('employment_type', 40)->nullable();
            $table->string('workplace_type', 20)->nullable();
            $table->string('seniority', 40)->nullable();
            $table->string('city')->nullable();
            $table->string('state', 100)->nullable();
            $table->char('country_code', 2)->nullable();
            $table->decimal('salary_min', 12, 2)->nullable();
            $table->decimal('salary_max', 12, 2)->nullable();
            $table->char('salary_currency', 3)->nullable();
            $table->string('salary_period', 20)->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->char('content_hash', 64)->nullable()->index();
            $table->json('raw_payload')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('imported_at')->useCurrent();
            $table->timestamps();

            $table->unique(['job_source_id', 'external_id']);
            $table->index(['workplace_type', 'employment_type']);
            $table->index(['country_code', 'state', 'city']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_listings');
    }
};
