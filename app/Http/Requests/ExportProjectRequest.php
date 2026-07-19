<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExportProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('view', $this->route('project'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sections' => ['sometimes', 'array'],
            'sections.generalInfo' => ['sometimes', 'boolean'],
            'sections.requirements' => ['sometimes', 'boolean'],
            'sections.team' => ['sometimes', 'boolean'],
            'sections.technicalChoices' => ['sometimes', 'boolean'],
            'sections.algorithmDiagrams' => ['sometimes', 'boolean'],
            'sections.tasks' => ['sometimes', 'boolean'],
            'sections.resources' => ['sometimes', 'boolean'],
        ];
    }
}
