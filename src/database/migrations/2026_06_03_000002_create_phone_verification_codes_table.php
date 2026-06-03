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
        Schema::create('phone_verification_codes', static function (Blueprint $table) {
            $table->id();
            $table->string('phone')->index()->comment('Номер телефона');
            $table->string('code', 255)->comment('Хеш кода подтверждения');
            $table->timestamp('expires_at')->comment('Срок действия кода');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phone_verification_codes');
    }
};
