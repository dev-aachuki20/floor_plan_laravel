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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->longText('data');
            $table->text('subject')->nullable();
            $table->longText('message')->nullable();
            $table->string('section')->nullable();
            $table->string('notification_type')->nullable();
           
            $table->unsignedBigInteger('rota_session_id')->nullable();
            $table->foreign('rota_session_id')->references('id')->on('rota_sessions');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users');

            $table->timestamp('read_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
