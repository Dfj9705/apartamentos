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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('apartment_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('common_area_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->date('reservation_date');

            $table->time('start_time');
            $table->time('end_time');

            $table->string('status', 20)
                ->default('confirmed');

            $table->text('notes')
                ->nullable();

            $table->timestamps();

            $table->index([
                'common_area_id',
                'reservation_date',
                'start_time',
                'end_time',
            ], 'reservations_area_schedule_index');

            $table->index([
                'user_id',
                'reservation_date',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
