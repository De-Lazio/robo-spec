<?php

namespace App\Domain\Projects\Services;

use App\Domain\Projects\Enums\ProjectStatus;
use App\Domain\Tasks\Enums\TaskStatus;
use App\Models\Project;

class ProjectProgressCalculator
{
    /**
     * Once a project has tasks, their completion ratio is the single source
     * of truth for progress, regardless of status. With zero tasks, progress
     * falls back to the project lifecycle status. Archived projects keep
     * whatever progress they had when archived, unless they have tasks.
     */
    public function calculate(Project $project, ProjectStatus $status, int $currentProgress): int
    {
        $total = $project->tasks()->count();

        if ($total > 0) {
            $done = $project->tasks()->where('status', TaskStatus::Done->value)->count();

            return (int) round($done / $total * 100);
        }

        return match ($status) {
            ProjectStatus::Draft => 0,
            ProjectStatus::InProgress => 40,
            ProjectStatus::Testing => 75,
            ProjectStatus::Completed => 100,
            ProjectStatus::Archived => $currentProgress,
        };
    }
}
