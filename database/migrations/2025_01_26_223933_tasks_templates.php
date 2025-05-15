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
        Schema::create('tasks_templates', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->string('title', 100); // Column for product name
            $table->integer('department_id');
            $table->string('created_by');
            $table->enum('type', ['Group', 'Solo'])->default('Solo');
            $table->integer('pages');
            $table->timestamps(); // Created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks_templates');
    }
};
