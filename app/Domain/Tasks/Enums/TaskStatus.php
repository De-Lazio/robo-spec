<?php

namespace App\Domain\Tasks\Enums;

enum TaskStatus: string
{
    case Todo = 'todo';
    case InProgress = 'in-progress';
    case Done = 'done';
}
