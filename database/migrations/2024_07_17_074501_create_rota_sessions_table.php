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
        Schema::create('rota_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rota_id')->constrained('rota');
            $table->foreignId('room_id')->constrained('rooms'); // Assuming you have a rooms table
            $table->enum('time_slot', ['AM', 'PM', 'EVE']);
            $table->foreignId('speciality_id')->constrained('specialities'); // Assuming you have a specialities table
            $table->date('week_day_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rota_sessions');
    }
};
