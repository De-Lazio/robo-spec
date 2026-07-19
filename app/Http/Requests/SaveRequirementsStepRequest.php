<?php

namespace App\Http\Requests;

use App\Domain\Requirements\Rules\RequirementsStepRules;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class SaveRequirementsStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project instanceof Project && ($this->user()?->can('editRequirements', $project) ?? false);
    }

    public function rules(): array
    {
        return RequirementsStepRules::rules((int) $this->route('step'));
    }
}
