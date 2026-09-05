<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->integer('attempt_number')->default(1);
            $table->dateTime('started_at');
            $table->dateTime('submitted_at')->nullable();
            $table->enum('status', ['in_progress', 'submitted', 'graded', 'abandoned'])->default('in_progress');
            $table->decimal('score', 8, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->unique(['exam_id', 'student_id', 'attempt_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
    }
};
