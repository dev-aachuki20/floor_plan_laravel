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
        Schema::table('users', function (Blueprint $table) {
            $table->string('google2fa_secret')->nullable()->after('user_email');
            $table->string('mfa_token')->nullable()->after('google2fa_secret');
            $table->datetime('mfa_expires_at')->nullable()->after('mfa_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('google2fa_secret');
            $table->dropColumn('mfa_token');
            $table->dropColumn('mfa_expires_at');
        });
    }
};
