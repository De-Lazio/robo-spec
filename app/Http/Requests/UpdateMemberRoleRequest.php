<?php

namespace App\Http\Requests;

use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Models\Project;
use App\Models\ProjectMember;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');
        $member = $this->route('member');

        return $project instanceof Project
            && $member instanceof ProjectMember
            && ($this->user()?->can('updateMemberRole', [$project, $member]) ?? false);
    }

    public function rules(): array
    {
        return [
            'role' => ['required', Rule::enum(ProjectMemberRole::class)->except(ProjectMemberRole::Owner)],
        ];
    }
}
