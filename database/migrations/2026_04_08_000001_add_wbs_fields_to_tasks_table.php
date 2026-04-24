<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('milestone_id')->constrained('tasks')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0)->after('completed_at');
            $table->string('wbs_code', 64)->nullable()->after('sort_order');
            $table->index(['milestone_id', 'parent_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'sort_order', 'wbs_code']);
        });
    }
};
