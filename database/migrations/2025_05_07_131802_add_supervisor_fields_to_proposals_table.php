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
        Schema::table('proposals', function (Blueprint $table) {
            $table->foreignId('proposed_supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('supervisor_response', ['PENDING', 'ACCEPTED', 'DECLINED'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropForeign(['proposed_supervisor_id']);
            $table->dropForeign(['supervisor_id']);
            $table->dropColumn(['proposed_supervisor_id', 'supervisor_id', 'supervisor_response']);
        });
    }
};
