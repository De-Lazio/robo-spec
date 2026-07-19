<?php

namespace App\Domain\Projects\DTOs;

use App\Domain\Projects\Enums\ProjectMemberRole;

readonly class InviteMemberData
{
    public function __construct(
        public string $email,
        public ProjectMemberRole $role,
    ) {}
}
