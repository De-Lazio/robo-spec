<?php

namespace App\Models;

use App\Domain\Organizations\Enums\OrganizationRole;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'description',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(OrganizationMember::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(OrganizationInvitation::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * @param  list<OrganizationRole>  $roles
     */
    public function hasRole(User $user, array $roles): bool
    {
        if ($user->is($this->owner)) {
            return in_array(OrganizationRole::Owner, $roles, true);
        }

        return $this->members()
            ->where('user_id', $user->getKey())
            ->whereIn('role', array_map(fn (OrganizationRole $role): string => $role->value, $roles))
            ->exists();
    }
}
