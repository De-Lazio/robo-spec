<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class LinkGitHubRepositoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project instanceof Project && ($this->user()?->can('manageGithub', $project) ?? false);
    }

    public function rules(): array
    {
        return [
            'url' => ['required', 'string', 'max:255'],
        ];
    }
}
