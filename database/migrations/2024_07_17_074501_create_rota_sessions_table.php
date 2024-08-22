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
            $table->uuid('uuid')->unique();
            $table->foreignId('quarter_id')->nullable()->constrained('quarters');
            $table->integer('week_no')->nullable();
            $table->foreignId('hospital_id')->constrained('hospital');
            $table->foreignId('room_id')->constrained('rooms');
            $table->enum('time_slot', ['AM', 'PM', 'EVE']);
            $table->foreignId('speciality_id')->nullable()->constrained('speciality');
            $table->date('week_day_date');
            $table->tinyInteger('status')->nullable()->comment('1 => At Risk, 2 => Closed');
            $table->foreignId('created_by')->constrained('users');
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
