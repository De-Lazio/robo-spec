<?php

namespace App\Http\Requests;

use App\Models\Project;
use App\Models\ProjectComponent;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');
        $technicalChoice = $this->route('technicalChoice');

        return $project instanceof Project
            && $technicalChoice instanceof ProjectComponent
            && ($this->user()?->can('manageTechnicalChoices', $project) ?? false);
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1'],
            'rationale' => ['nullable', 'string', 'max:1000'],
            'linked_function_ids' => ['nullable', 'array'],
            'linked_function_ids.*' => ['string'],
        ];
    }
}
