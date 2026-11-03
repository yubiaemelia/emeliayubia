<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('passenger_cards', function (Blueprint $table) {
            $table->id();
            $table->string('passenger_card_no')->unique();
            $table->enum('card_type', ['Regular', 'Student', 'Senior']);
            $table->decimal('card_balance', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('passenger_cards');
    }
};