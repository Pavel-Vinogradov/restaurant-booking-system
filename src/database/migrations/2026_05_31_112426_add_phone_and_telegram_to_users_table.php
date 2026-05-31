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
        Schema::table('users', static function (Blueprint $table) {
            $table->comment = 'Пользователи системы';
            $table->string('phone')->nullable()->after('email')->comment('Номер телефона пользователя');
            $table->string('telegram_id')->nullable()->unique()->after('phone')->comment('Telegram ID пользователя');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', static function (Blueprint $table) {
            $table->dropColumn(['phone', 'telegram_id']);
        });
    }
};
