<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class StoreLocalComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project instanceof Project && ($this->user()?->can('manageTechnicalChoices', $project) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'component_category_id' => ['required', 'integer', 'exists:component_categories,id'],
            'name' => ['required', 'string', 'max:160'],
            'manufacturer' => ['nullable', 'string', 'max:120'],
            'reference' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'specs' => ['nullable', 'array'],
            'specs.*.key' => ['required_with:specs', 'string', 'max:60'],
            'specs.*.value' => ['required_with:specs', 'string', 'max:200'],
            'price_cents' => ['nullable', 'integer', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'supplier_url' => ['nullable', 'url', 'max:255'],
        ];
    }
}
