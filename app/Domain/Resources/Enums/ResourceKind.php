<?php

namespace App\Domain\Resources\Enums;

enum ResourceKind: string
{
    case Image = 'image';
    case Document = 'document';
    case Code = 'code';
    case Cad = 'cad';
    case Schema = 'schema';
    case Archive = 'archive';
    case Other = 'other';
}
