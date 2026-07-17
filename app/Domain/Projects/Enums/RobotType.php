<?php

namespace App\Domain\Projects\Enums;

enum RobotType: string
{
    case Mobile = 'mobile';
    case Arm = 'arm';
    case Drone = 'drone';
    case Fixed = 'fixed';
    case Humanoid = 'humanoid';
    case Other = 'other';
}
