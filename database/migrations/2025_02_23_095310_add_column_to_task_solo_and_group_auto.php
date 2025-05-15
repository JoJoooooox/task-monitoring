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
        Schema::table('task_solo_auto', function (Blueprint $table) {
            $table->integer('due'); // Change as needed
        });
        Schema::table('task_group_auto', function (Blueprint $table) {
            $table->integer('due'); // Change as needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_solo_auto', function (Blueprint $table) {
            $table->dropColumn('due');
        });
        Schema::table('task_group_auto', function (Blueprint $table) {
            $table->dropColumn('due');
        });
    }
};
