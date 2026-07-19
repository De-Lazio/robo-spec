<?php

namespace App\Http\Requests;

use App\Domain\Tasks\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskStatusRequest extends FormRequest
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
            'status' => ['required', Rule::in(array_map(fn (TaskStatus $status): string => $status->value, TaskStatus::cases()))],
        ];
    }
}
