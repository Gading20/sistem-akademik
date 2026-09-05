<?php

namespace App\Enums;

enum ExamAttemptStatusEnum: string
{
    case IN_PROGRESS = 'in_progress';
    case SUBMITTED = 'submitted';
    case GRADED = 'graded';
    case ABANDONED = 'abandoned';

    public function label(): string
    {
        return match ($this) {
            self::IN_PROGRESS => 'In Progress',
            self::SUBMITTED => 'Submitted',
            self::GRADED => 'Graded',
            self::ABANDONED => 'Abandoned',
        };
    }
}
