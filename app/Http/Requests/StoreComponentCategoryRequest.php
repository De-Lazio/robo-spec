<?php

namespace App\Http\Requests;

use App\Domain\Components\Enums\ComponentType;
use App\Models\ComponentCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreComponentCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ComponentCategory::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(ComponentType::class)],
            'name' => ['required', 'string', 'max:80'],
        ];
    }
}
