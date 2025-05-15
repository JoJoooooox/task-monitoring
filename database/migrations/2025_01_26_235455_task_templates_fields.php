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
        Schema::create('task_templates_fields', function (Blueprint $table) {
            $table->id();
            $table->integer('template_id');
            $table->string('field_name', 100);
            $table->enum('field_type', ['Text', 'Date', 'Checkbox', 'Dropdown', 'Textarea', 'Radio', 'File']);
            $table->boolean('is_required')->default(false);
            $table->text('options')->nullable(); // For dropdown options
            $table->timestamps();
            $table->integer('field_row');
            $table->integer('field_page');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_templates_fields');
    }
};
