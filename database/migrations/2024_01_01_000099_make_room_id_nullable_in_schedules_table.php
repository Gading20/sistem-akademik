<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->foreignId('room_id')->nullable()->change();
            // MySQL tidak mengizinkan drop index yang dipakai foreign key;
            // sediakan index pengganti pada room_id terlebih dahulu.
            $table->index('room_id', 'schedules_room_id_index');
            $table->dropUnique(['room_id', 'day', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->foreignId('room_id')->nullable(false)->change();
            $table->unique(['room_id', 'day', 'start_time']);
            $table->dropIndex('schedules_room_id_index');
        });
    }
};
