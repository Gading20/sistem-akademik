<?php

namespace App\Enums;

enum ExamStatusEnum: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PUBLISHED => 'Published',
            self::ACTIVE => 'Active',
            self::COMPLETED => 'Completed',
            self::ARCHIVED => 'Archived',
        };
    }
}
