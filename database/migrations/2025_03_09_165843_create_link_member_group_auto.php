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
        Schema::create('link_member_group_auto', function (Blueprint $table) {
            $table->id();
            $table->integer('group_id');
            $table->integer('template_id');
            $table->integer('link_id');
            $table->integer('user_id'); // Column for product name
            $table->integer('adder_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('link_member_group_auto');
    }
};
