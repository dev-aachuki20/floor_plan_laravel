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
        Schema::create('rota', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('quarter_id')->constrained('quarters');
            $table->foreignId('hospital_id')->constrained('hospitals'); // Assuming you have a hospitals table
            $table->integer('week_no');
            $table->date('week_start_date');
            $table->date('week_end_date');
            $table->boolean('session_released')->default(false);
            $table->text('session_description')->nullable();
            $table->foreignId('created_by')->constrained('users'); // Assuming you have a users table
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rota');
    }
};
