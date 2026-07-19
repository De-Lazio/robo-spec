<?php

namespace App\Domain\Requirements\Enums;

enum RequirementsStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
}
