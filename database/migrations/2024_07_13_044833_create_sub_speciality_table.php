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
        Schema::create('sub_speciality', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('parent_speciality_id');
            $table->foreign('parent_speciality_id')->references('id')->on('speciality');

            $table->string('sub_speciality_name');
            $table->longText('sub_speciality_description')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_speciality');
    }
};
