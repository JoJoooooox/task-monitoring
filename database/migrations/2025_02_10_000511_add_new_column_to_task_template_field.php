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
        Schema::table('task_templates_fields', function (Blueprint $table) {
            $table->string('field_label')->nullable();
            $table->string('field_description')->nullable();
            $table->string('field_pre_answer')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_templates_fields', function (Blueprint $table) {
            $table->dropColumn('field_label');
            $table->dropColumn('field_description');
            $table->dropColumn('field_pre_answer');
        });
    }
};
