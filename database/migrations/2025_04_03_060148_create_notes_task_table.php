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
        Schema::create('notes_task', function (Blueprint $table) {
            $table->id();
            $table->integer('task_id')->nullable();
            $table->integer('user_id');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('type');
            $table->text('notes')->nullable();
            $table->integer('sort');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes_task');
    }
};
