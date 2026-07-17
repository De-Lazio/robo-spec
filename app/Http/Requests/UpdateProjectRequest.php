<?php

namespace App\Http\Requests;

use App\Domain\Projects\Enums\ProjectStatus;
use App\Domain\Projects\Enums\RobotType;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project instanceof Project && ($this->user()?->can('update', $project) ?? false);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:5000'],
            'robot_type' => ['required', Rule::enum(RobotType::class)],
            'domain' => ['nullable', 'string', 'max:120'],
            'status' => ['required', Rule::enum(ProjectStatus::class)],
            'tags' => ['nullable', 'array', 'max:12'],
            'tags.*' => ['string', 'max:40', 'distinct:ignore_case'],
        ];
    }
}
