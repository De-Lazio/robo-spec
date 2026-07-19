<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class SaveRequirementsDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project instanceof Project && ($this->user()?->can('editRequirements', $project) ?? false);
    }

    public function rules(): array
    {
        return [
            'data' => ['required', 'array'],
        ];
    }
}
