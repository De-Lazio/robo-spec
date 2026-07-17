<?php

namespace App\Domain\Projects\Enums;

enum ProjectStatus: string
{
    case Draft = 'draft';
    case InProgress = 'in_progress';
    case Testing = 'testing';
    case Completed = 'completed';
    case Archived = 'archived';
}
