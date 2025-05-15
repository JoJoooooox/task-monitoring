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
        Schema::table('task_fields', function (Blueprint $table) {
            $table->integer('field_row');
            $table->integer('field_page');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_fields', function (Blueprint $table) {
            $table->dropColumn('field_row');
            $table->dropColumn('field_page');
        });
    }
};
