<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('proposals', function (Blueprint $table) {
        $table->id();
        $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
        $table->string('title');
        $table->text('description');
        $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
        $table->text('committee_feedback')->nullable();
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
