<?php

namespace App\Domain\Integrations\GitHub\Enums;

enum SyncStatus: string
{
    case Pending = 'pending';
    case Synced = 'synced';
    case Failed = 'failed';
}
