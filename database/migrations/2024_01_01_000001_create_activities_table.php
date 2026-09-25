<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = config('activity.table', 'activities');

        Schema::create($table, function (Blueprint $blueprint): void {
            $blueprint->uuid('id')->primary();

            $blueprint->string('owner_type', 191);
            $blueprint->string('owner_id', 191);

            $blueprint->string('activity_type');
            $blueprint->string('description')->nullable();
            $blueprint->json('data')->nullable();
            $blueprint->json('metadata')->nullable();

            $blueprint->timestamps();
            $blueprint->softDeletes();

            $blueprint->index(['owner_type', 'owner_id'], 'activities_owner_index');
            $blueprint->index('activity_type');
            $blueprint->index('created_at');
        });
    }

    public function down(): void
    {
        $table = config('activity.table', 'activities');

        Schema::dropIfExists($table);
    }
};
