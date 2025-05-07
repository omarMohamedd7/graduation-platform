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
        // Modify the supervisor_response enum to include NONE and make it the default
        DB::statement("ALTER TABLE proposals MODIFY COLUMN supervisor_response ENUM('NONE', 'PENDING', 'ACCEPTED', 'DECLINED') DEFAULT 'NONE'");
        
        // Update any NULL values to 'NONE'
        DB::statement("UPDATE proposals SET supervisor_response = 'NONE' WHERE supervisor_response IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to allowing NULL values with the original options
        DB::statement("ALTER TABLE proposals MODIFY COLUMN supervisor_response ENUM('PENDING', 'ACCEPTED', 'DECLINED') DEFAULT NULL");
    }
};
