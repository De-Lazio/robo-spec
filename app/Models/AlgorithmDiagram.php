<?php

namespace App\Models;

use App\Domain\AlgorithmDiagrams\Enums\DiagramFormalism;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlgorithmDiagram extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'project_id',
        'name',
        'formalism',
        'data',
        'microcontroller_component_id',
        'energy_source_component_id',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'formalism' => DiagramFormalism::class,
            'data' => 'array',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function microcontroller(): BelongsTo
    {
        return $this->belongsTo(ProjectComponent::class, 'microcontroller_component_id');
    }

    public function energySource(): BelongsTo
    {
        return $this->belongsTo(ProjectComponent::class, 'energy_source_component_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
