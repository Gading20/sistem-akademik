<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->enum('type', ['quiz', 'pre_test', 'post_test', 'assessment', 'mid_test', 'pts', 'pas', 'practical_exam', 'project_exam', 'final_exam']);
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->integer('duration_minutes');
            $table->integer('attempt_limit')->default(1);
            $table->boolean('random_question')->default(false);
            $table->boolean('random_option')->default(false);
            $table->boolean('shuffle_options')->default(false);
            $table->boolean('show_result')->default(false);
            $table->boolean('show_answer_after_submit')->default(false);
            $table->decimal('passing_score', 5, 2)->default(60);
            $table->string('token')->nullable();
            $table->enum('status', ['draft', 'published', 'active', 'completed', 'archived'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
