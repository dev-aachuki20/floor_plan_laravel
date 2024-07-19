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

            $table->unsignedBigInteger('hospital_id');
            $table->foreign('hospital_id')->references('id')->on('hospital');

            $table->unsignedBigInteger('user_id');//owner_id
            $table->foreign('user_id')->references('id')->on('users');

            $table->unsignedBigInteger('procedure_id');
            $table->foreign('procedure_id')->references('id')->on('procedures');

            $table->string('time_slot')->nullable();

            $table->unsignedBigInteger('status_id');
            $table->foreign('status_id')->references('id')->on('session_status');

            $table->datetime('scheduled')->nullable();

            $table->longText('session_description')->nullable();

            $table->boolean('session_released');

            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            
            $table->timestamps();
            $table->softDeletes();
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
