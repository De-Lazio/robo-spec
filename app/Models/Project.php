<?php

namespace App\Models;

use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Domain\Projects\Enums\ProjectStatus;
use App\Domain\Projects\Enums\RobotType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'owner_id',
        'organization_id',
        'name',
        'slug',
        'description',
        'robot_type',
        'domain',
        'status',
        'progress',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'robot_type' => RobotType::class,
            'status' => ProjectStatus::class,
            'progress' => 'integer',
            'archived_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(ProjectTag::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ProjectActivity::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(ProjectInvitation::class);
    }

    public function requirementsDocuments(): HasMany
    {
        return $this->hasMany(RequirementsDocument::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class);
    }

    public function githubRepositories(): HasMany
    {
        return $this->hasMany(GithubRepository::class);
    }

    public function technicalChoices(): HasMany
    {
        return $this->hasMany(ProjectComponent::class);
    }

    public function hasRole(User $user, array $roles): bool
    {
        if ($user->is($this->owner)) {
            return in_array(ProjectMemberRole::Owner, $roles, true);
        }

        return $this->members()
            ->where('user_id', $user->getKey())
            ->whereIn('role', array_map(fn (ProjectMemberRole $role): string => $role->value, $roles))
            ->exists();
    }
}
