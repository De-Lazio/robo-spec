<?php

namespace App\Domain\Components\Enums;

enum ComponentType: string
{
    case Microcontroller = 'microcontroller';
    case Sensor = 'sensor';
    case Actuator = 'actuator';
    case PreActuator = 'pre_actuator';
    case EnergySource = 'energy_source';
    case Effector = 'effector';
}
