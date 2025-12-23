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
        Schema::create('data_nodes', function (Blueprint $table) {
            $table->id(); // ID узла [cite: 3]
            $table->string('name'); // Название [cite: 3]
            $table->unsignedBigInteger('value')->default(0); // Значение для аналитики [cite: 24]
            $table->string('type')->nullable(); // Тип (city, school, class) [cite: 24]
            $table->text('description')->nullable(); // Описание [cite: 24]
            $table->json('meta_data')->nullable(); // Цвет, иконки и прочее [cite: 24]
            $table->timestamps();

            $table->index('type'); // Индекс для быстрой фильтрации [cite: 24]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_nodes');
    }
};
