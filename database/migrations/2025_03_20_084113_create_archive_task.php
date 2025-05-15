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
        Schema::create('archive_task', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100); // Column for product name
            $table->integer('task_id');
            $table->integer('department_id');
            $table->integer('template_id');
            $table->integer('link_id')->nullable();
            $table->date('assigned')->nullable();
            $table->date('due')->nullable();
            $table->string('assigned_to')->nullable();
            $table->string('assigned_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->enum('type', ['Group', 'Solo'])->default('Solo');
            $table->decimal('progress_percentage', 5, 2)->default(0.00);
            $table->enum('status', ['Pending', 'Ongoing', 'To Check', 'Completed', 'Distributed', 'Linked'])->default('Pending');
            $table->integer('pages')->nullable();
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archive_task');
    }
};
