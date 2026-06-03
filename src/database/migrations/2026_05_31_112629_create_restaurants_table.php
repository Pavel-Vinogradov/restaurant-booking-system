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
        Schema::create('restaurants', static function (Blueprint $table) {
            $table->comment = 'Рестораны';
            $table->id()->comment('Идентификатор ресторана');
            $table->string('name')->comment('Название ресторана');
            $table->string('address')->comment('Адрес ресторана');
            $table->string('phone')->comment('Контактный телефон');
            $table->string('timezone')->default('Europe/Moscow')->comment('Часовой пояс ресторана');
            $table->string('status')->default('active')->comment('Статус ресторана (active/inactive)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
