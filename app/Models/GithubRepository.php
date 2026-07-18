<?php

namespace App\Models;

use App\Domain\Integrations\GitHub\Enums\SyncStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GithubRepository extends Model
{
    protected $table = 'github_repositories';

    protected $fillable = [
        'project_id',
        'provider',
        'owner',
        'repository',
        'url',
        'default_branch',
        'visibility',
        'last_synced_at',
        'sync_status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'sync_status' => SyncStatus::class,
            'metadata' => 'array',
            'last_synced_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
