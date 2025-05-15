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
        Schema::create('task_distribute_auto', function (Blueprint $table) {
            $table->id();
            $table->integer('template_id'); // Column for product name
            $table->integer('department_id');
            $table->integer('to_department_id');
            $table->integer('task_id');
            $table->integer('adder_id');
            $table->string('title')->nullable();
            $table->enum('status', ['Pending','Accepted', 'Declined'])->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_distribute_auto');
    }
};
