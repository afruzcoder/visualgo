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
        Schema::create('data_node_closure', function (Blueprint $table) {
            $table->unsignedBigInteger('ancestor_id'); // Предок [cite: 3]
            $table->unsignedBigInteger('descendant_id'); // Потомок [cite: 3]
            $table->unsignedInteger('depth'); // Глубина связи [cite: 3]

            // Составной первичный ключ для защиты от дублей [cite: 25]
            $table->primary(['ancestor_id', 'descendant_id']);

            // Внешние ключи с каскадным удалением [cite: 25]
            $table->foreign('ancestor_id')->references('id')->on('data_nodes')->onDelete('cascade');
            $table->foreign('descendant_id')->references('id')->on('data_nodes')->onDelete('cascade');

            $table->index(['ancestor_id', 'depth']); // Важно для быстрой выборки детей [cite: 25]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_node_closure');
    }
};
