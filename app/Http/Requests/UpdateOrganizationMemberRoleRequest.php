<?php

namespace App\Http\Requests;

use App\Domain\Organizations\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\OrganizationMember;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrganizationMemberRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $organization = $this->route('organization');
        $member = $this->route('member');

        return $organization instanceof Organization
            && $member instanceof OrganizationMember
            && ($this->user()?->can('updateMemberRole', [$organization, $member]) ?? false);
    }

    public function rules(): array
    {
        return [
            'role' => ['required', Rule::enum(OrganizationRole::class)->except(OrganizationRole::Owner)],
        ];
    }
}
