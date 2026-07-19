<?php

namespace App\Http\Requests;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');
        $task = $this->route('task');

        return $project instanceof Project
            && $task instanceof Task
            && ($this->user()?->can('manageTasks', $project) ?? false);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:4000'],
            'assignee_id' => ['nullable', 'integer', $this->assigneeIsProjectMember()],
            'due_date' => ['nullable', 'date'],
            'linked_function_ids' => ['nullable', 'array'],
            'linked_function_ids.*' => ['string'],
        ];
    }

    private function assigneeIsProjectMember(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            /** @var Project $project */
            $project = $this->route('project');

            if ($value === null) {
                return;
            }

            $isMember = $project->owner_id === $value || $project->members()->where('user_id', $value)->exists();

            if (! $isMember) {
                $fail('Le membre assigné doit faire partie du projet.');
            }
        };
    }
}
