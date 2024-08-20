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
        Schema::create('rota_session_quarters', function (Blueprint $table) {
            
            $table->id();
            $table->integer('quarter_no')->nullable();
            $table->integer('quarter_year')->nullable();
            $table->foreignId('hospital_id')->constrained('hospital');
            $table->foreignId('room_id')->constrained('rooms');
            $table->enum('time_slot', ['AM', 'PM', 'EVE']);
            $table->string('day_name');
            $table->foreignId('speciality_id')->nullable()->constrained('speciality');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['quarter_no', 'quarter_year','hospital_id','room_id','time_slot','day_name'], 'quarter_index');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rota_session_quarters');
    }
};
