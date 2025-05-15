<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('task_solo_auto', function (Blueprint $table) {
            $table->dropColumn(['day', 'time']);

            $table->string('start_day')->nullable(); // Stores 'Monday', 'Tuesday', etc.
            $table->string('end_day')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_solo_auto', function (Blueprint $table) {
            $table->dropColumn(['start_day', 'end_day', 'start_time', 'end_time']);

            // Re-add old columns
            $table->string('day')->nullable();
            $table->time('time')->nullable();
        });
    }
};
