<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable()->after('status');
            $table->unsignedInteger('closure_duration_days')->nullable()->after('completed_at');
            $table->text('closure_performance_notes')->nullable()->after('closure_duration_days');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['completed_at', 'closure_duration_days', 'closure_performance_notes']);
        });
    }
};
