<?php

namespace App\Enums;

enum ExamTypeEnum: string
{
    case QUIZ = 'quiz';
    case PRE_TEST = 'pre_test';
    case POST_TEST = 'post_test';
    case ASSESSMENT = 'assessment';
    case MID_TEST = 'mid_test';
    case PTS = 'pts';
    case PAS = 'pas';
    case PRACTICAL_EXAM = 'practical_exam';
    case PROJECT_EXAM = 'project_exam';
    case FINAL_EXAM = 'final_exam';

    public function label(): string
    {
        return match ($this) {
            self::QUIZ => 'Quiz',
            self::PRE_TEST => 'Pre Test',
            self::POST_TEST => 'Post Test',
            self::ASSESSMENT => 'Assessment',
            self::MID_TEST => 'Mid Test',
            self::PTS => 'PTS',
            self::PAS => 'PAS',
            self::PRACTICAL_EXAM => 'Practical Exam',
            self::PROJECT_EXAM => 'Project Exam',
            self::FINAL_EXAM => 'Final Exam',
        };
    }
}
