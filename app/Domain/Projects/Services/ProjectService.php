<?php

namespace App\Domain\Projects\Services;

use App\Domain\Projects\Contracts\ProjectActivityRepositoryInterface;
use App\Domain\Projects\Contracts\ProjectMembershipRepositoryInterface;
use App\Domain\Projects\Contracts\ProjectRepositoryInterface;
use App\Domain\Projects\DTOs\CreateProjectData;
use App\Domain\Projects\DTOs\UpdateProjectData;
use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Domain\Projects\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectService
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly ProjectMembershipRepositoryInterface $memberships,
        private readonly ProjectActivityRepositoryInterface $activities,
        private readonly ProjectProgressCalculator $progress,
    ) {}

    public function create(User $owner, CreateProjectData $data): Project
    {
        return DB::transaction(function () use ($owner, $data): Project {
            $project = $this->projects->create([
                'owner_id' => $owner->getKey(),
                'organization_id' => $data->organizationId,
                'name' => trim($data->name),
                'slug' => $this->uniqueSlug($data->name),
                'description' => $this->nullableTrimmedValue($data->description),
                'robot_type' => $data->robotType,
                'domain' => $this->nullableTrimmedValue($data->domain),
                'status' => ProjectStatus::Draft,
                'progress' => $this->progress->calculate(ProjectStatus::Draft, 0),
            ]);

            $this->memberships->create([
                'project_id' => $project->getKey(),
                'user_id' => $owner->getKey(),
                'role' => ProjectMemberRole::Owner,
                'joined_at' => now(),
            ]);

            $project->tags()->createMany(
                collect($data->tags)
                    ->map(fn (string $tag): string => trim($tag))
                    ->filter()
                    ->unique(fn (string $tag): string => Str::lower($tag))
                    ->take(12)
                    ->map(fn (string $tag): array => ['name' => $tag])
                    ->values()
                    ->all(),
            );

            $this->activities->create([
                'project_id' => $project->getKey(),
                'actor_id' => $owner->getKey(),
                'event' => 'project.created',
                'subject_type' => Project::class,
                'subject_id' => $project->getKey(),
                'properties' => ['name' => $project->name],
            ]);

            return $project->load(['owner', 'members.user', 'tags']);
        });
    }

    public function update(Project $project, User $actor, UpdateProjectData $data): Project
    {
        return DB::transaction(function () use ($project, $actor, $data): Project {
            $project->update([
                'name' => trim($data->name),
                'slug' => $this->uniqueSlug($data->name, $project),
                'description' => $this->nullableTrimmedValue($data->description),
                'robot_type' => $data->robotType,
                'domain' => $this->nullableTrimmedValue($data->domain),
                'status' => $data->status,
                'progress' => $this->progress->calculate($data->status, $project->progress),
                'archived_at' => $data->status === ProjectStatus::Archived ? now() : null,
            ]);

            $project->tags()->delete();
            $project->tags()->createMany($this->tagAttributes($data->tags));
            $this->recordActivity($project, $actor, 'project.updated');

            return $project->fresh(['owner', 'tags']);
        });
    }

    public function archive(Project $project, User $actor): void
    {
        $project->update(['status' => ProjectStatus::Archived, 'archived_at' => now()]);
        $this->recordActivity($project, $actor, 'project.archived');
    }

    public function restore(Project $project, User $actor): void
    {
        $project->update([
            'status' => ProjectStatus::Draft,
            'progress' => $this->progress->calculate(ProjectStatus::Draft, $project->progress),
            'archived_at' => null,
        ]);
        $this->recordActivity($project, $actor, 'project.restored');
    }

    public function delete(Project $project, User $actor): void
    {
        DB::transaction(function () use ($project, $actor): void {
            $this->recordActivity($project, $actor, 'project.deleted');
            $project->delete();
        });
    }

    private function uniqueSlug(string $name, ?Project $except = null): string
    {
        $baseSlug = Str::slug($name) ?: 'project';
        $slug = $baseSlug;
        $suffix = 2;

        while (Project::query()->where('slug', $slug)->when($except, fn ($query) => $query->whereKeyNot($except))->exists()) {
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

    /**
     * @param  list<string>  $tags
     * @return list<array{name: string}>
     */
    private function tagAttributes(array $tags): array
    {
        return collect($tags)
            ->map(fn (string $tag): string => trim($tag))
            ->filter()
            ->unique(fn (string $tag): string => Str::lower($tag))
            ->take(12)
            ->map(fn (string $tag): array => ['name' => $tag])
            ->values()
            ->all();
    }

    private function recordActivity(Project $project, User $actor, string $event): void
    {
        $this->activities->create([
            'project_id' => $project->getKey(),
            'actor_id' => $actor->getKey(),
            'event' => $event,
            'subject_type' => Project::class,
            'subject_id' => $project->getKey(),
            'properties' => ['name' => $project->name],
        ]);
    }
}
