<?php

namespace App\Http\Requests;

use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project instanceof Project && ($this->user()?->can('inviteMembers', $project) ?? false);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'role' => ['required', Rule::enum(ProjectMemberRole::class)->except(ProjectMemberRole::Owner)],
        ];
    }
}
