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
        Schema::create('task_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->string('field_name', 100);
            $table->enum('field_type', ['Text', 'Date', 'Checkbox', 'Dropdown', 'Textarea', 'Radio', 'File']);
            $table->boolean('is_required')->default(false);
            $table->text('options')->nullable(); // For dropdown options
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_fields');
    }
};
