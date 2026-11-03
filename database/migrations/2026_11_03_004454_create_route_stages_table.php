<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_stages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('route_id')
                ->constrained('routes')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('stage_name');
            $table->unsignedInteger('stage_order');

            $table->timestamps();

            $table->unique(['route_id', 'stage_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_stages');
    }
};