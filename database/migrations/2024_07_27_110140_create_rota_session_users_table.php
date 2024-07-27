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
        Schema::create('rota_session_users', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users'); // Assuming you have a users table
            $table->foreignId('rota_session_id')->constrained('rota_sessions');
            $table->foreignId('role_id')->constrained('roles'); // Assuming you have a roles table
            $table->tinyInteger('status')->default(0)->comment('0=>Pending, 1=>Confirm, 2=>Decline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rota_session_users');
    }
};
