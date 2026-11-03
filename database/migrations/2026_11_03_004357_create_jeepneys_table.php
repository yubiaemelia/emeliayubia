<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jeepneys', function (Blueprint $table) {
            $table->id();
            $table->string('jeepney_plate')->unique();

            $table->foreignId('route_id')
                ->constrained('routes')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jeepneys');
    }
};