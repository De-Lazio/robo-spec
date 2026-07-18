<?php

namespace App\Domain\Projects\Services;

use App\Domain\Projects\Enums\ProjectStatus;

class ProjectProgressCalculator
{
    /**
     * Progress is derived from the project lifecycle status until the CDC
     * publication, milestones and resources feed a finer-grained signal
     * (see docs/PLAN_IMPLEMENTATION_ROBOFORGE.md, phases 4-5). Centralizing
     * it here keeps it out of controllers/forms as a single source of truth.
     * Archived projects keep whatever progress they had when archived.
     */
    public function calculate(ProjectStatus $status, int $currentProgress): int
    {
        return match ($status) {
            ProjectStatus::Draft => 0,
            ProjectStatus::InProgress => 40,
            ProjectStatus::Testing => 75,
            ProjectStatus::Completed => 100,
            ProjectStatus::Archived => $currentProgress,
        };
    }
}
