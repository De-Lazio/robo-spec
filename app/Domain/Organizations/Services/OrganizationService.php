<?php

namespace App\Domain\Organizations\Services;

use App\Domain\Organizations\Contracts\OrganizationMembershipRepositoryInterface;
use App\Domain\Organizations\Contracts\OrganizationRepositoryInterface;
use App\Domain\Organizations\DTOs\CreateOrganizationData;
use App\Domain\Organizations\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrganizationService
{
    public function __construct(
        private readonly OrganizationRepositoryInterface $organizations,
        private readonly OrganizationMembershipRepositoryInterface $memberships,
    ) {}

    public function create(User $owner, CreateOrganizationData $data): Organization
    {
        return DB::transaction(function () use ($owner, $data): Organization {
            $organization = $this->organizations->create([
                'owner_id' => $owner->getKey(),
                'name' => trim($data->name),
                'slug' => $this->uniqueSlug($data->name),
                'description' => $this->nullableTrimmedValue($data->description),
            ]);

            $this->memberships->create([
                'organization_id' => $organization->getKey(),
                'user_id' => $owner->getKey(),
                'role' => OrganizationRole::Owner,
                'joined_at' => now(),
            ]);

            return $organization->load('owner');
        });
    }

    public function update(Organization $organization, string $name, ?string $description): Organization
    {
        return $this->organizations->update($organization, [
            'name' => trim($name),
            'slug' => $this->uniqueSlug($name, $organization),
            'description' => $this->nullableTrimmedValue($description),
        ]);
    }

    public function delete(Organization $organization): void
    {
        DB::transaction(function () use ($organization): void {
            // The FK's nullOnDelete() only fires on a real SQL DELETE, but
            // Organization is soft-deleted (an UPDATE), so projects must be
            // detached explicitly to actually become personal projects again.
            $organization->projects()->update(['organization_id' => null]);
            $this->organizations->delete($organization);
        });
    }

    private function uniqueSlug(string $name, ?Organization $except = null): string
    {
        $baseSlug = Str::slug($name) ?: 'organization';
        $slug = $baseSlug;
        $suffix = 2;

        while (Organization::query()->where('slug', $slug)->when($except, fn ($query) => $query->whereKeyNot($except))->exists()) {
            $slug = sprintf('%s-%d', $baseSlug, $suffix);
            $suffix++;
        }

        return $slug;
    }

    private function nullableTrimmedValue(?string $value): ?string
    {
        $value = $value === null ? null : trim($value);

        return $value === '' ? null : $value;
    }
}
