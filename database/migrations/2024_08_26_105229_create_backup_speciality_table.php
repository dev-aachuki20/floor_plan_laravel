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
        Schema::create('backup_speciality', function (Blueprint $table) {
            $table->id();
            $table->foreignId('speciality_id')->nullable()->constrained('speciality');
            $table->foreignId('hospital_id')->nullable()->constrained('hospital');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_speciality');
    }
};
