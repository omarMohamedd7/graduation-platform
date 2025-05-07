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
        Schema::table('users', function (Blueprint $table) {
            // Rename 'name' to 'full_name'
            $table->renameColumn('name', 'full_name');
            
            // Add 'role' and 'department' columns
            $table->enum('role', ['STUDENT', 'SUPERVISOR', 'COMMITTEE_HEAD'])->after('password');
            $table->string('department')->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert changes
            $table->renameColumn('full_name', 'name');
            $table->dropColumn(['role', 'department']);
        });
    }
}; 