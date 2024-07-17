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
        Schema::create('sessions_log', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('session_id');
            $table->foreign('session_id')->references('id')->on('rota_sessions');

            $table->unsignedBigInteger('offering_id');
            $table->foreign('offering_id')->references('id')->on('sessions_offering');

            $table->datetime('action_time')->nullable();

            $table->unsignedBigInteger('role');
            $table->foreign('role')->references('id')->on('roles');

            $table->string('booking_status')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions_log');
    }
};
