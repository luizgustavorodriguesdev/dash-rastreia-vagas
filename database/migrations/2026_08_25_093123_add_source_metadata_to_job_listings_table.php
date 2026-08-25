<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            $table->string('category')->nullable()->after('title')->index();
            $table->string('location_text')->nullable()->after('country_code')->index();
            $table->string('salary_text')->nullable()->after('salary_period');
        });
    }

    public function down(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            $table->dropColumn(['category', 'location_text', 'salary_text']);
        });
    }
};
