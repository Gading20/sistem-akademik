<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->decimal('tugas_score', 5, 2)->nullable();
            $table->decimal('quiz_score', 5, 2)->nullable();
            $table->decimal('uts_score', 5, 2)->nullable();
            $table->decimal('uas_score', 5, 2)->nullable();
            $table->decimal('practical_score', 5, 2)->nullable();
            $table->decimal('project_score', 5, 2)->nullable();
            $table->decimal('final_score', 5, 2)->nullable();
            $table->decimal('final_percentage', 5, 2)->nullable();
            $table->string('letter_grade')->nullable();
            $table->boolean('is_remedial')->default(false);
            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'class_id', 'academic_year_id', 'semester_id', 'is_remedial'], 'grades_scope_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
