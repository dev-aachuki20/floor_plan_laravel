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
        Schema::create('user_sub_speciality', function (Blueprint $table) {
          
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->unsignedBigInteger('speciality_id');
            $table->foreign('speciality_id')->references('id')->on('speciality');

            $table->unsignedBigInteger('sub_speciality_id');
            $table->foreign('sub_speciality_id')->references('id')->on('sub_speciality');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_sub_speciality');
    }
};
