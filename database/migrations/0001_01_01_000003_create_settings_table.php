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
        Schema::create('settings', function (Blueprint $table) {

            $table->id();
            $table->string('key')->default(null)->nullable();
            $table->longText('value')->default(null)->nullable();
            $table->string('type',100)->default(null)->nullable();
            $table->string('display_name')->default(null)->nullable();
            $table->text('details')->default(null)->nullable();
            $table->enum('group', ['site', 'support'])->nullable();
            $table->tinyInteger('status')->default(1)->comment('0=> inactive, 1=> active');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->default(Null)->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
