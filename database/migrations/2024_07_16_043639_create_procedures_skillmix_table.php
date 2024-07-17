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
        Schema::create('procedures_skillmix', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('procedures_id');
            $table->foreign('procedures_id')->references('id')->on('procedures');

            $table->unsignedBigInteger('speciality_id');
            $table->foreign('speciality_id')->references('id')->on('procedures');

            $table->unsignedBigInteger('sub_speciality_id');
            $table->foreign('sub_speciality_id')->references('id')->on('procedures');


        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procedures_skillmix');
    }
};
