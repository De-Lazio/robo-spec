<?php

namespace App\Http\Requests;

use App\Models\Component;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Component::class) ?? false;
    }

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
            'datasheet' => ['nullable', File::default()->max(config('roboforge.components.datasheet.max_upload_kb'))->extensions(['pdf'])],
            'price_cents' => ['nullable', 'integer', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'supplier_url' => ['nullable', 'url', 'max:255'],
            'image_url' => ['nullable', 'url', 'max:500'],
            'image' => ['nullable', File::default()->max(config('roboforge.components.image.max_upload_kb'))->extensions(['jpg', 'jpeg', 'png', 'webp', 'gif'])],
        ];
    }
}
