<?php

namespace App\Models;

use App\Domain\Requirements\Enums\RequirementsStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequirementsDocument extends Model
{
    protected $fillable = [
        'project_id',
        'version',
        'status',
        'current_step',
        'data',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => RequirementsStatus::class,
            'data' => 'array',
            'published_at' => 'datetime',
            'version' => 'integer',
            'current_step' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function isPublished(): bool
    {
        return $this->status === RequirementsStatus::Published;
    }
}
