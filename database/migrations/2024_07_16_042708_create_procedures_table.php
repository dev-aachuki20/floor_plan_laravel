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
        Schema::create('procedures', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('finance_id');
            $table->foreign('finance_id')->references('id')->on('finance');

            $table->string('procedures_name');
            $table->longText('procedures_description')->nullable();

            $table->unsignedBigInteger('required_roles');

            $table->timestamps();
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procedures');
    }
};
