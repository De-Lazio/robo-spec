<?php

namespace App\Domain\Requirements\Rules;

use Illuminate\Validation\Rule;
use InvalidArgumentException;

class RequirementsStepRules
{
    public const TOTAL_STEPS = 8;

    public const USER_OPTIONS = ['Élèves', 'Étudiants', 'Techniciens', 'Agriculteurs', 'Personnel médical', 'Industrie', 'Grand public', 'Chercheurs', 'Militaire'];

    public const SAFETY_CONSTRAINTS = ["Arrêt d'urgence physique", 'Protection contre la surchauffe', 'Respect des normes IEC 60950', 'Protection contre les courts-circuits', 'Signalisation lumineuse et sonore', 'Isolation électrique complète', 'Limiteur de courant', 'Détection de chute'];

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
            7 => [
                'criteria' => ['required', 'array', 'min:1'],
                'criteria.*.name' => ['required', 'string', 'max:160'],
                'criteria.*.value' => ['required', 'string', 'max:160'],
            ],
            8 => [
                'expectedResult' => ['required', 'string', 'max:2000'],
                'successCriteria' => ['required', 'string', 'max:2000'],
                'testMethod' => ['nullable', 'string', 'max:2000'],
            ],
            default => throw new InvalidArgumentException("Unknown requirements step: {$step}"),
        };
    }
}
