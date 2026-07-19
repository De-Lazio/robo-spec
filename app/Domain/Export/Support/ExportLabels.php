<?php

namespace App\Domain\Export\Support;

use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Domain\Projects\Enums\ProjectStatus;
use App\Domain\Projects\Enums\RobotType;
use App\Domain\Resources\Enums\ResourceCategory;
use App\Domain\Tasks\Enums\TaskStatus;

class ExportLabels
{
    public static function role(ProjectMemberRole $role): string
    {
        return match ($role) {
            ProjectMemberRole::Owner => 'Propriétaire',
            ProjectMemberRole::Manager => 'Manager',
            ProjectMemberRole::Mechanical => 'Contributeur mécanique',
            ProjectMemberRole::Electronics => 'Contributeur électronique',
            ProjectMemberRole::Software => 'Contributeur logiciel',
            ProjectMemberRole::Contributor => 'Contributeur',
            ProjectMemberRole::Viewer => 'Observateur',
        };
    }

    public static function projectStatus(ProjectStatus $status): string
    {
        return match ($status) {
            ProjectStatus::Draft => 'Brouillon',
            ProjectStatus::InProgress => 'En cours',
            ProjectStatus::Testing => 'Tests',
            ProjectStatus::Completed => 'Terminé',
            ProjectStatus::Archived => 'Archivé',
        };
    }

    public static function robotType(RobotType $type): string
    {
        return match ($type) {
            RobotType::Mobile => 'Robot mobile',
            RobotType::Arm => 'Bras robotisé',
            RobotType::Drone => 'Drone',
            RobotType::Fixed => 'Robot fixe',
            RobotType::Humanoid => 'Humanoïde',
            RobotType::Other => 'Autre',
        };
    }

    public static function taskStatus(TaskStatus $status): string
    {
        return match ($status) {
            TaskStatus::Todo => 'À faire',
            TaskStatus::InProgress => 'En cours',
            TaskStatus::Done => 'Terminé',
        };
    }

    public static function resourceCategory(ResourceCategory $category): string
    {
        return match ($category) {
            ResourceCategory::Mechanical => 'Mécanique',
            ResourceCategory::Electronics => 'Électronique',
            ResourceCategory::Software => 'Informatique',
            ResourceCategory::Other => 'Autre',
        };
    }
}
