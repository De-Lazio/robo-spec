<?php

namespace App\Http\Requests;

use App\Models\ComponentCategory;
use Illuminate\Foundation\Http\FormRequest;

class UpdateComponentCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $category = $this->route('category');

        return $category instanceof ComponentCategory && ($this->user()?->can('update', $category) ?? false);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
        ];
    }
}
