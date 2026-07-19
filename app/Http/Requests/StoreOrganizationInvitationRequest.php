<?php

namespace App\Http\Requests;

use App\Domain\Organizations\Enums\OrganizationRole;
use App\Models\Organization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrganizationInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $organization = $this->route('organization');

        return $organization instanceof Organization && ($this->user()?->can('inviteMembers', $organization) ?? false);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'role' => ['required', Rule::enum(OrganizationRole::class)->except(OrganizationRole::Owner)],
        ];
    }
}
