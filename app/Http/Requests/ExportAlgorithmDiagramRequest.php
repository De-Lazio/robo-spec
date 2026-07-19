<?php

namespace App\Http\Requests;

use App\Domain\Resources\Enums\ResourceCategory;
use App\Models\AlgorithmDiagram;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class ExportAlgorithmDiagramRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');
        $diagram = $this->route('algorithmDiagram');

        return $project instanceof Project
            && $diagram instanceof AlgorithmDiagram
            && ($this->user()?->can('manageAlgorithmDiagrams', $project) ?? false);
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                File::default()
                    ->max(config('roboforge.resources.max_upload_kb'))
                    ->extensions(['png']),
            ],
            'category' => ['required', Rule::in(array_map(fn (ResourceCategory $case): string => $case->value, ResourceCategory::cases()))],
        ];
    }
}
