<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grading_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->enum('method', ['automatic', 'manual'])->default('automatic');
            $table->decimal('tugas_weight', 5, 2)->default(20);
            $table->decimal('quiz_weight', 5, 2)->default(10);
            $table->decimal('uts_weight', 5, 2)->default(20);
            $table->decimal('uas_weight', 5, 2)->default(30);
            $table->decimal('practical_weight', 5, 2)->default(10);
            $table->decimal('project_weight', 5, 2)->default(10);
            $table->timestamps();

            $table->unique(['subject_id', 'class_id', 'academic_year_id', 'semester_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grading_configs');
    }
};
