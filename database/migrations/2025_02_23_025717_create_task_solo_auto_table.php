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
        Schema::create('task_solo_auto', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('adder_id');
            $table->enum('type', ['week', 'day', 'time']);
            $table->string('day')->nullable(); // Stores 'Monday', 'Tuesday', etc.
            $table->time('time')->nullable(); // Stores time value
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_solo_auto');
    }
};
