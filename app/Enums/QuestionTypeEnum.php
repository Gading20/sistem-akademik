<?php

namespace App\Enums;

enum QuestionTypeEnum: string
{
    case MCQ = 'mcq';
    case MCQ_COMPLEX = 'mcq_complex';
    case TRUE_FALSE = 'true_false';
    case MATCHING = 'matching';
    case SHORT_ANSWER = 'short_answer';
    case ESSAY = 'essay';
    case FILE_UPLOAD = 'file_upload';
    case PRACTICAL = 'practical';

    public function label(): string
    {
        return match ($this) {
            self::MCQ => 'Multiple Choice',
            self::MCQ_COMPLEX => 'Multiple Choice Complex',
            self::TRUE_FALSE => 'True / False',
            self::MATCHING => 'Menjodohkan',
            self::SHORT_ANSWER => 'Isian Singkat',
            self::ESSAY => 'Essay',
            self::FILE_UPLOAD => 'Upload File',
            self::PRACTICAL => 'Soal Praktik',
        };
    }
}
