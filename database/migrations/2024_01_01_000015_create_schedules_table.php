<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teaching_assignment_id')->constrained('teaching_assignments')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->enum('day', ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu']);
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();

            $table->unique(['room_id', 'day', 'start_time']);
            $table->unique(['teaching_assignment_id', 'day', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
