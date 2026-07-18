<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project instanceof Project && ($this->user()?->can('manageTechnicalChoices', $project) ?? false);
    }

    public function rules(): array
    {
        return [
            'component_id' => ['required', 'string', 'exists:components,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'rationale' => ['nullable', 'string', 'max:1000'],
            'linked_function_ids' => ['nullable', 'array'],
            'linked_function_ids.*' => ['string'],
        ];
    }
}
