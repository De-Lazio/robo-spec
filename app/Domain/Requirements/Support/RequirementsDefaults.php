<?php

namespace App\Domain\Requirements\Support;

class RequirementsDefaults
{
    /**
     * @return array<string, mixed>
     */
    public static function make(): array
    {
        return [
            'step1' => ['projectName' => '', 'team' => '', 'date' => now()->toDateString(), 'projectType' => '', 'supervisor' => ''],
            'step2' => ['context' => '', 'problem' => '', 'whyRobot' => ''],
            'step3' => ['missions' => ['']],
            'step4' => ['users' => [], 'otherUsers' => ''],
            'step5' => ['functions' => [
                ['id' => 'F1', 'name' => ''],
                ['id' => 'F2', 'name' => ''],
                ['id' => 'F3', 'name' => ''],
                ['id' => 'F4', 'name' => ''],
            ]],
            'step6' => [
                'maxSize' => '', 'maxWeight' => '', 'minAutonomy' => '', 'minSpeed' => '',
                'maxBudget' => '', 'estimatedCost' => '', 'safetyConstraints' => [],
                'temperature' => '', 'usageConditions' => '',
            ],
            'step7' => ['criteria' => [
                ['name' => 'Autonomie', 'value' => ''],
                ['name' => 'Vitesse', 'value' => ''],
                ['name' => 'Charge utile', 'value' => ''],
                ['name' => 'Distance de détection', 'value' => ''],
                ['name' => 'Précision de positionnement', 'value' => ''],
            ]],
            'step8' => ['expectedResult' => '', 'successCriteria' => '', 'testMethod' => ''],
        ];
    }
}
