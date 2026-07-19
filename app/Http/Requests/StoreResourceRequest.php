<?php

namespace App\Http\Requests;

use App\Domain\Resources\Enums\ResourceCategory;
use App\Domain\Resources\Support\ResourceKindResolver;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project instanceof Project && ($this->user()?->can('uploadResources', $project) ?? false);
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                File::default()
                    ->max(config('roboforge.resources.max_upload_kb'))
                    ->extensions(ResourceKindResolver::allowedExtensions()),
            ],
            'category' => ['required', Rule::in(array_map(fn (ResourceCategory $case): string => $case->value, ResourceCategory::cases()))],
            'description' => ['nullable', 'string', 'max:500'],
            'folder' => ['nullable', 'string', 'max:120'],
        ];
    }
}
