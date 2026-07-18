<?php

namespace App\Domain\Organizations\DTOs;

use App\Domain\Organizations\Enums\OrganizationRole;

readonly class InviteOrganizationMemberData
{
    public function __construct(
        public string $email,
        public OrganizationRole $role,
    ) {}
}
