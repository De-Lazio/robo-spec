<?php

namespace App\Domain\Projects\Enums;

enum ProjectMemberRole: string
{
    case Owner = 'owner';
    case Manager = 'manager';
    case Mechanical = 'mechanical';
    case Electronics = 'electronics';
    case Software = 'software';
    case Contributor = 'contributor';
    case Viewer = 'viewer';

    public function canManageProject(): bool
    {
        return in_array($this, [self::Owner, self::Manager], true);
    }
}
