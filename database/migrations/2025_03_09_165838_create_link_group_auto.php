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
        Schema::create('link_group_auto', function (Blueprint $table) {
            $table->id();
            $table->integer('template_id');
            $table->integer('link_id');
            $table->integer('user_id'); // Column for product name
            $table->integer('adder_id');
            $table->string('start_day', 255);
            $table->string('end_day', 255);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('due');
            $table->enum('type', ['week', 'day', 'time'])->default('time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('link_group_auto');
    }
};
