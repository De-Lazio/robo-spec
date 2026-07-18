<?php

namespace App\Domain\Requirements\Rules;

use Illuminate\Validation\Rule;
use InvalidArgumentException;

class RequirementsStepRules
{
    public const USER_OPTIONS = ['Élèves', 'Étudiants', 'Techniciens', 'Agriculteurs', 'Personnel médical', 'Industrie', 'Grand public', 'Chercheurs', 'Militaire'];

    public const CONTROL_UNITS = ['Arduino Uno', 'Arduino Mega', 'Arduino Nano', 'ESP8266', 'ESP32', 'Raspberry Pi', 'Raspberry Pi 4', 'STM32', 'PIC', 'BeagleBone', 'Jetson Nano', 'Autre'];

    public const ENERGY_SOURCES = ['Batterie Li-Po', 'Batterie Li-Ion', 'Batterie NiMH', 'Pile alcaline', 'Alimentation secteur', 'Panneau solaire', 'Supercondensateur', 'Hybride'];

    public const SAFETY_CONSTRAINTS = ["Arrêt d'urgence physique", 'Protection contre la surchauffe', 'Respect des normes IEC 60950', 'Protection contre les courts-circuits', 'Signalisation lumineuse et sonore', 'Isolation électrique complète', 'Limiteur de courant', 'Détection de chute'];

    public const PLANNING_STATUSES = ['pending', 'in-progress', 'done'];

    /**
     * @return array<string, mixed>
     */
    public static function rules(int $step): array
    {
        return match ($step) {
            1 => [
                'projectName' => ['required', 'string', 'max:160'],
                'team' => ['required', 'string', 'max:160'],
                'date' => ['required', 'date'],
                'projectType' => ['nullable', 'string', 'max:60'],
                'supervisor' => ['nullable', 'string', 'max:160'],
            ],
            2 => [
                'context' => ['required', 'string', 'max:4000'],
                'problem' => ['required', 'string', 'max:4000'],
                'whyRobot' => ['required', 'string', 'max:4000'],
            ],
            3 => [
                'missions' => ['required', 'array', 'min:1'],
                'missions.*' => ['required', 'string', 'max:300'],
            ],
            4 => [
                'users' => ['array', 'required_without:otherUsers'],
                'users.*' => ['string', Rule::in(self::USER_OPTIONS)],
                'otherUsers' => ['nullable', 'string', 'max:190', 'required_without:users'],
            ],
            5 => [
                'functions' => ['required', 'array', 'min:1'],
                'functions.*.id' => ['required', 'string', 'distinct'],
                'functions.*.name' => ['required', 'string', 'max:160'],
            ],
            6 => [
                'capteurs' => ['required', 'array', 'min:1'],
                'capteurs.*.name' => ['required', 'string', 'max:160'],
                'capteurs.*.role' => ['nullable', 'string', 'max:300'],
                'controlUnit' => ['required', 'string', 'max:60'],
                'otherControlUnit' => ['nullable', 'string', 'max:160'],
                'actionneurs' => ['required', 'array', 'min:1'],
                'actionneurs.*.name' => ['required', 'string', 'max:160'],
                'actionneurs.*.role' => ['nullable', 'string', 'max:300'],
                'energySource' => ['required', 'string', 'max:60'],
            ],
            7 => [
                'maxSize' => ['nullable', 'string', 'max:120'],
                'maxWeight' => ['nullable', 'string', 'max:120'],
                'minAutonomy' => ['nullable', 'string', 'max:120'],
                'minSpeed' => ['nullable', 'string', 'max:120'],
                'maxBudget' => ['nullable', 'string', 'max:120'],
                'estimatedCost' => ['nullable', 'string', 'max:120'],
                'safetyConstraints' => ['required', 'array', 'min:1'],
                'safetyConstraints.*' => ['string'],
                'temperature' => ['nullable', 'string', 'max:120'],
                'usageConditions' => ['nullable', 'string', 'max:300'],
            ],
            8 => [
                'criteria' => ['required', 'array', 'min:1'],
                'criteria.*.name' => ['required', 'string', 'max:160'],
                'criteria.*.value' => ['required', 'string', 'max:160'],
            ],
            9 => [
                'missionLabel' => ['required', 'string', 'max:160'],
                'perceptionLabel' => ['required', 'string', 'max:160'],
                'decisionLabel' => ['required', 'string', 'max:160'],
                'actionLabel' => ['required', 'string', 'max:160'],
                'feedbackLabel' => ['required', 'string', 'max:160'],
            ],
            10 => [
                'materials' => ['required', 'array', 'min:1'],
                'materials.*.component' => ['required', 'string', 'max:160'],
                'materials.*.quantity' => ['required', 'numeric', 'min:0'],
                'materials.*.reference' => ['nullable', 'string', 'max:120'],
            ],
            11 => [
                'planning' => ['required', 'array', 'min:1'],
                'planning.*.stage' => ['required', 'string', 'max:160'],
                'planning.*.date' => ['nullable', 'date'],
                'planning.*.status' => ['required', Rule::in(self::PLANNING_STATUSES)],
            ],
            12 => [
                'expectedResult' => ['required', 'string', 'max:2000'],
                'successCriteria' => ['required', 'string', 'max:2000'],
                'testMethod' => ['nullable', 'string', 'max:2000'],
            ],
            default => throw new InvalidArgumentException("Unknown requirements step: {$step}"),
        };
    }
}
