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
        Schema::create('archive_task_fields', function (Blueprint $table) {
            $table->id();
            $table->integer('task_id');
            $table->string('field_name', 100);
            $table->enum('field_type', ['Text', 'Date', 'Checkbox', 'Dropdown', 'Textarea', 'Radio', 'File', 'Typography']);
            $table->boolean('is_required')->default(false);
            $table->text('options')->nullable(); // For dropdown options
            $table->integer('field_page');
            $table->string('field_label', 100)->nullable();
            $table->string('field_description', 100)->nullable();
            $table->string('field_pre_answer', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archive_task_fields');
    }
};
