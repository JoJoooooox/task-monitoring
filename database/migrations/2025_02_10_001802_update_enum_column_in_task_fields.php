<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE task_fields MODIFY field_type ENUM('Text', 'Date', 'Checkbox', 'Dropdown', 'Textarea', 'Radio', 'File', 'Typography') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE task_fields MODIFY field_type ENUM('Text', 'Date', 'Checkbox', 'Dropdown', 'Textarea', 'Radio', 'File') NOT NULL");
    }
};
