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
        Schema::create('opening_hours', static function (Blueprint $table) {
            $table->comment = 'Часы работы ресторанов';
            $table->id()->comment('Идентификатор записи');
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('day_of_week')->comment('День недели (0=Пн, 6=Вс)');
            $table->time('open_time')->comment('Время открытия');
            $table->time('close_time')->comment('Время закрытия');
            $table->boolean('is_closed')->default(false)->comment('Закрыто в этот день');
            $table->timestamps();

            $table->unique(['restaurant_id', 'day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opening_hours');
    }
};
