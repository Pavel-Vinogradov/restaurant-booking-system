<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->string('number')->comment('Номер стола');
            $table->integer('capacity')->comment('Вместимость');
            $table->string('location')->nullable()->comment('Расположение (зал, веранда и т.д.)');
            $table->boolean('is_available')->default(true)->comment('Доступен для бронирования');
            $table->text('description')->nullable()->comment('Описание стола');
            $table->decimal('min_order_amount', 10, 2)->nullable()->comment('Минимальная сумма заказа');
            $table->json('features')->nullable()->comment('Особенности стола (например, ["window", "quiet"])');
            $table->timestamps();

            $table->unique(['restaurant_id', 'number']);
            $table->index(['restaurant_id', 'is_available']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
