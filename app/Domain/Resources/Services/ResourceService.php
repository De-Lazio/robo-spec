<?php

namespace App\Domain\Resources\Services;

use App\Domain\Notifications\Services\NotificationService;
use App\Domain\Projects\Contracts\ProjectActivityRepositoryInterface;
use App\Domain\Resources\Contracts\ResourceRepositoryInterface;
use App\Domain\Resources\DTOs\UploadResourceData;
use App\Domain\Resources\Support\ResourceKindResolver;
use App\Models\Project;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ResourceService
{
    public function __construct(
        private readonly ResourceRepositoryInterface $resources,
        private readonly ProjectActivityRepositoryInterface $activities,
        private readonly NotificationService $notifications,
    ) {}

    public function upload(Project $project, User $actor, UploadResourceData $data): Resource
    {
        $extension = mb_strtolower($data->file->getClientOriginalExtension());
        $kind = ResourceKindResolver::fromExtension($extension);

        if ($kind === null) {
            throw ValidationException::withMessages([
                'file' => ["L'extension .{$extension} n'est pas autorisée."],
            ]);
        }

        $strictPrefixes = config("roboforge.resources.strict_mime_kinds.{$kind->value}");
        $realMimeType = $data->file->getMimeType() ?? 'application/octet-stream';

        if ($strictPrefixes !== null) {
            $matches = collect($strictPrefixes)->contains(fn (string $prefix) => str_starts_with($realMimeType, $prefix));

            if (! $matches) {
                throw ValidationException::withMessages([
                    'file' => ['Le contenu du fichier ne correspond pas à son extension.'],
                ]);
            }
        }

        $originalName = $this->sanitizeFileName($data->file->getClientOriginalName());
        $disk = config('roboforge.resources.disk');
        $ulid = (string) Str::ulid();
        $directory = "projects/{$project->id}/resources/{$ulid}";
        $checksum = hash_file('sha256', $data->file->getRealPath());

        $storedPath = $data->file->storeAs($directory, $originalName, ['disk' => $disk]);
        $folder = $this->normalizeFolder($data->folder);

        $activity = null;

        try {
            $resource = DB::transaction(function () use ($project, $actor, $data, $kind, $ulid, $disk, $storedPath, $originalName, $realMimeType, $checksum, $folder, &$activity): Resource {
                $resource = $this->resources->create([
                    'id' => $ulid,
                    'project_id' => $project->getKey(),
                    'algorithm_diagram_id' => $data->algorithmDiagramId,
                    'uploaded_by' => $actor->getKey(),
                    'category' => $data->category,
                    'folder' => $folder,
                    'kind' => $kind,
                    'name' => $originalName,
                    'original_name' => $originalName,
                    'disk' => $disk,
                    'path' => $storedPath,
                    'mime_type' => $realMimeType,
                    'size_bytes' => $data->file->getSize(),
                    'description' => $data->description,
                    'checksum' => $checksum,
                ]);

                $activity = $this->activities->create([
                    'project_id' => $project->getKey(),
                    'actor_id' => $actor->getKey(),
                    'event' => 'resource.uploaded',
                    'subject_type' => Resource::class,
                    'subject_id' => $resource->getKey(),
                    'properties' => ['name' => $originalName, 'category' => $data->category->value],
                ]);

                return $resource;
            });
        } catch (Throwable $e) {
            Storage::disk($disk)->delete($storedPath);

            throw $e;
        }

        $this->notifications->notifyProjectEvent($activity);

        return $resource;
    }

    public function moveToFolder(Resource $resource, User $actor, ?string $folder): Resource
    {
        $folder = $this->normalizeFolder($folder);
        $activity = null;

        $resource = DB::transaction(function () use ($resource, $actor, $folder, &$activity): Resource {
            $resource = $this->resources->update($resource, ['folder' => $folder]);

            $activity = $this->activities->create([
                'project_id' => $resource->project_id,
                'actor_id' => $actor->getKey(),
                'event' => 'resource.updated',
                'subject_type' => Resource::class,
                'subject_id' => $resource->getKey(),
                'properties' => ['name' => $resource->name, 'folder' => $folder],
            ]);

            return $resource;
        });

        $this->notifications->notifyProjectEvent($activity);

        return $resource;
    }

    public function delete(Resource $resource, User $actor): void
    {
        $activity = null;

        DB::transaction(function () use ($resource, $actor, &$activity): void {
            $this->resources->delete($resource);

            $activity = $this->activities->create([
                'project_id' => $resource->project_id,
                'actor_id' => $actor->getKey(),
                'event' => 'resource.deleted',
                'subject_type' => Resource::class,
                'subject_id' => $resource->getKey(),
                'properties' => ['name' => $resource->name],
            ]);
        });

        $this->notifications->notifyProjectEvent($activity);
    }

    private function normalizeFolder(?string $folder): ?string
    {
        $folder = trim((string) $folder);

        return $folder === '' ? null : Str::limit($folder, 120, '');
    }

    private function sanitizeFileName(string $name): string
    {
        $name = str_replace(['/', '\\', "\0"], '', $name);
        $name = ltrim($name, '.');
        $name = trim($name);

        if ($name === '') {
            $name = 'fichier';
        }

        return Str::limit($name, 180, '');
    }
}
