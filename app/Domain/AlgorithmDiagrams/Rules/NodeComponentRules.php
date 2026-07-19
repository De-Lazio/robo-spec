<?php

namespace App\Domain\AlgorithmDiagrams\Rules;

use App\Domain\Components\Enums\ComponentType;

class NodeComponentRules
{
    public const NODE_TYPES = [
        // Algorigramme
        'start', 'end', 'process', 'decision', 'io', 'wait', 'calc', 'communication', 'comment',
        // GRAFCET
        'initial_step', 'step', 'transition', 'or_divergence', 'or_convergence', 'and_divergence', 'and_convergence',
    ];

    /**
     * Node types that accept any component type from the project's nomenclature —
     * no ComponentType maps specifically to a communication module, so we stay
     * permissive rather than invent a restriction the catalog doesn't support.
     */
    private const UNRESTRICTED_TYPES = ['communication'];

    /**
     * @return list<ComponentType>|null null means unrestricted (any type allowed)
     */
    public static function allowedComponentTypes(string $nodeType): ?array
    {
        if (in_array($nodeType, self::UNRESTRICTED_TYPES, true)) {
            return null;
        }

        return match ($nodeType) {
            'process', 'step', 'initial_step' => [ComponentType::Actuator, ComponentType::PreActuator, ComponentType::Effector],
            'decision', 'transition' => [ComponentType::Sensor],
            'io' => [ComponentType::Sensor, ComponentType::Actuator, ComponentType::PreActuator, ComponentType::Effector],
            default => [],
        };
    }
}
