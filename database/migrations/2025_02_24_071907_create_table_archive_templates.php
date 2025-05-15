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
        Schema::create('table_archive_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100); // Column for product name
            $table->integer('department_id');
            $table->string('created_by');
            $table->enum('type', ['Group', 'Solo'])->default('Solo');
            $table->integer('pages');
            $table->enum('stepper', ['Yes', 'No'])->default('No');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_archive_templates');
    }
};
