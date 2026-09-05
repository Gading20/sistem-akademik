<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_bank_id')->constrained('question_banks')->cascadeOnDelete();
            $table->enum('type', ['mcq', 'mcq_complex', 'true_false', 'matching', 'short_answer', 'essay', 'file_upload', 'practical']);
            $table->text('question');
            $table->text('explanation')->nullable();
            $table->enum('difficulty', ['easy', 'medium', 'hard']);
            $table->decimal('points', 5, 2)->default(1);
            $table->string('topic')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
