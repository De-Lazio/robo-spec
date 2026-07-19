<?php

namespace App\Http\Requests;

use App\Domain\Projects\Enums\RobotType;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Project::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:5000'],
            'robot_type' => ['required', Rule::enum(RobotType::class)],
            'domain' => ['nullable', 'string', 'max:120'],
            'tags' => ['nullable', 'array', 'max:12'],
            'tags.*' => ['string', 'max:40', 'distinct:ignore_case'],
            'organization_id' => ['nullable', 'string', 'exists:organizations,id'],
        ];
    }
}
