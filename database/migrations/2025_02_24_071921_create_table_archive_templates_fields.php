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
        Schema::create('table_archive_templates_fields', function (Blueprint $table) {
            $table->id();
            $table->integer('template_id');
            $table->string('field_name', 255)->nullable();
            $table->enum('field_type', ['Text', 'Date', 'Checkbox', 'Dropdown', 'Textarea', 'Typography', 'Radio', 'File']);
            $table->boolean('is_required')->default(false);
            $table->text('options')->nullable();
            $table->integer('field_page');
            $table->integer('field_label')->nullable();
            $table->string('field_description', 255)->nullable();
            $table->string('field_pre_answer', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_archive_templates_fields');
    }
};
