<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->string('tower')->nullable();
            $table->unsignedInteger('floor')->nullable();

            $table->enum('status', [
                'occupied',
                'available',
                'maintenance',
            ])->default('available');

            $table->timestamps();

            $table->unique(['tower', 'number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartments');
    }
};
