<?php

namespace App\Domain\Resources\Enums;

enum ResourceCategory: string
{
    case Mechanical = 'mechanical';
    case Electronics = 'electronics';
    case Software = 'software';
    case Other = 'other';
}
