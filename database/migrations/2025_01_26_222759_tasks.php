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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->string('title', 100); // Column for product name
            $table->integer('department_id');
            $table->integer('template_id');
            $table->date('assigned');
            $table->date('due');
            $table->string('assigned_to');
            $table->string('assigned_by');
            $table->enum('type', ['Group', 'Solo'])->default('Solo');
            $table->decimal('progress_percentage', 5, 2)->default(0.00); // Column for price
            $table->enum('status', ['Pending', 'Ongoing', 'Completed'])->default('Pending');
            $table->timestamps(); // Created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
