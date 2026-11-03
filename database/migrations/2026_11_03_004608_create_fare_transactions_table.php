<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fare_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('passenger_card_id')
                ->constrained('passenger_cards')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('jeepney_id')
                ->constrained('jeepneys')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('boarding_stage_id')
                ->constrained('route_stages')
                ->restrictOnDelete();

            $table->foreignId('alighting_stage_id')
                ->constrained('route_stages')
                ->restrictOnDelete();

            $table->decimal('distance_km', 8, 2);
            $table->decimal('calculated_fare', 10, 2);
            $table->decimal('balance_before', 10, 2);
            $table->decimal('balance_after', 10, 2);

            $table->dateTime('tapped_in_at');
            $table->dateTime('tapped_out_at');

            $table->timestamps();

            $table->index('tapped_in_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fare_transactions');
    }
};