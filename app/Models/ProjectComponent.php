<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'component_id',
        'quantity',
        'rationale',
        'linked_function_ids',
        'added_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'linked_function_ids' => 'array',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
