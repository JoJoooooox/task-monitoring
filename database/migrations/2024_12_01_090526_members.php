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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('department_id');
            $table->string('user_id');
            $table->enum('role', [
                'observer',
                'employee',
                'intern'
            ])->default('employee');
            $table->string('name');
            $table->enum('status', [
                'active',
                'inactive'
            ])->default('active');
            $table->string('profile');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
