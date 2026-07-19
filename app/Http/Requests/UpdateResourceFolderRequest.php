<?php

namespace App\Http\Requests;

use App\Models\Project;
use App\Models\Resource;
use Illuminate\Foundation\Http\FormRequest;

class UpdateResourceFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');
        $resource = $this->route('resource');

        if (! $project instanceof Project || ! $resource instanceof Resource) {
            return false;
        }

        return $this->user()?->can('updateResource', [$project, $resource]) ?? false;
    }

    public function rules(): array
    {
        return [
            'folder' => ['nullable', 'string', 'max:120'],
        ];
    }
}
