<?php

namespace App\Http\Requests;

use App\Domain\AlgorithmDiagrams\Rules\NodeComponentRules;
use App\Models\AlgorithmDiagram;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAlgorithmDiagramRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:160'],

            'data' => ['required', 'array'],

            'data.nodes' => ['present', 'array'],
            'data.nodes.*.id' => ['required', 'string'],
            'data.nodes.*.type' => ['required', Rule::in(NodeComponentRules::NODE_TYPES)],
            'data.nodes.*.position.x' => ['required', 'numeric'],
            'data.nodes.*.position.y' => ['required', 'numeric'],
            'data.nodes.*.data.label' => ['nullable', 'string', 'max:160'],
            'data.nodes.*.data.comment' => ['nullable', 'string', 'max:1000'],
            'data.nodes.*.data.componentIds' => ['nullable', 'array'],
            'data.nodes.*.data.componentIds.*' => ['integer'],

            'data.edges' => ['present', 'array'],
            'data.edges.*.id' => ['required', 'string'],
            'data.edges.*.source' => ['required', 'string'],
            'data.edges.*.target' => ['required', 'string'],
            'data.edges.*.label' => ['nullable', 'string', 'max:120'],
            'data.edges.*.style' => ['nullable', Rule::in(['default', 'loop'])],
            'data.edges.*.points' => ['nullable', 'array'],
            'data.edges.*.points.*.x' => ['required', 'numeric'],
            'data.edges.*.points.*.y' => ['required', 'numeric'],

            'data.variables' => ['nullable', 'array'],
            'data.variables.*.id' => ['required', 'string'],
            'data.variables.*.name' => ['required', 'string', 'max:80'],

            'data.edgeStrokeWidth' => ['nullable', 'integer', 'min:1', 'max:8'],

            'microcontroller_component_id' => ['nullable', 'integer'],
            'energy_source_component_id' => ['nullable', 'integer'],
        ];
    }
}
