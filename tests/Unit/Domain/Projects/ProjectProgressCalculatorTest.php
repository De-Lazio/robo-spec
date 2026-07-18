<?php

namespace Tests\Unit\Domain\Projects;

use App\Domain\Projects\Enums\ProjectStatus;
use App\Domain\Projects\Services\ProjectProgressCalculator;
use PHPUnit\Framework\TestCase;

class ProjectProgressCalculatorTest extends TestCase
{
    public function test_it_maps_each_status_to_a_deterministic_progress(): void
    {
        $calculator = new ProjectProgressCalculator;

        $this->assertSame(0, $calculator->calculate(ProjectStatus::Draft, 60));
        $this->assertSame(40, $calculator->calculate(ProjectStatus::InProgress, 0));
        $this->assertSame(75, $calculator->calculate(ProjectStatus::Testing, 0));
        $this->assertSame(100, $calculator->calculate(ProjectStatus::Completed, 0));
    }

    public function test_archived_projects_keep_their_current_progress(): void
    {
        $calculator = new ProjectProgressCalculator;

        $this->assertSame(75, $calculator->calculate(ProjectStatus::Archived, 75));
        $this->assertSame(0, $calculator->calculate(ProjectStatus::Archived, 0));
    }
}
