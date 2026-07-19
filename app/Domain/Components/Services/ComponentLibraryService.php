<?php

namespace App\Domain\Components\Services;

use App\Domain\Components\Contracts\ComponentCategoryRepositoryInterface;
use App\Domain\Components\Contracts\ComponentRepositoryInterface;
use App\Domain\Components\DTOs\ComponentData;
use App\Domain\Components\DTOs\CreateComponentCategoryData;
use App\Models\Component;
use App\Models\ComponentCategory;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ComponentLibraryService
{
    public function __construct(
        private readonly ComponentCategoryRepositoryInterface $categories,
        private readonly ComponentRepositoryInterface $components,
    ) {}

    public function createCategory(CreateComponentCategoryData $data): ComponentCategory
    {
        return $this->categories->create([
            'type' => $data->type,
            'name' => trim($data->name),
            'slug' => Str::slug($data->name) ?: 'categorie',
        ]);
    }

    public function updateCategory(ComponentCategory $category, string $name): ComponentCategory
    {
        return $this->categories->update($category, [
            'name' => trim($name),
            'slug' => Str::slug($name) ?: 'categorie',
        ]);
    }

    public function deleteCategory(ComponentCategory $category): void
    {
        if ($category->components()->exists()) {
            throw ValidationException::withMessages([
                'category' => ['Cette catégorie contient encore des composants ; retirez-les avant de la supprimer.'],
            ]);
        }

        $this->categories->delete($category);
    }

    public function createComponent(User $actor, ComponentData $data): Component
    {
        $attributes = $this->baseAttributes($data);
        $attributes['created_by'] = $actor->getKey();

        if ($data->datasheet === null && $data->image === null) {
            return $this->components->create($attributes);
        }

        return $this->persistWithFiles(null, $attributes, $data);
    }

    public function createLocalComponent(Project $project, User $actor, ComponentData $data): Component
    {
        $component = $this->createComponent($actor, $data);

        return $this->components->update($component, ['owner_project_id' => $project->getKey()]);
    }

    public function updateComponent(Component $component, ComponentData $data): Component
    {
        $attributes = $this->baseAttributes($data);

        if ($data->datasheet === null && $data->image === null) {
            return $this->components->update($component, $attributes);
        }

        return $this->persistWithFiles($component, $attributes, $data);
    }

    public function deactivateComponent(Component $component): Component
    {
        return $this->components->update($component, ['is_active' => false]);
    }

    public function reactivateComponent(Component $component): Component
    {
        return $this->components->update($component, ['is_active' => true]);
    }

    public function deleteComponent(Component $component): void
    {
        if ($component->projectComponents()->exists()) {
            throw ValidationException::withMessages([
                'component' => ['Ce composant est encore utilisé dans des choix techniques ; retirez-le avant de le supprimer.'],
            ]);
        }

        if ($component->datasheet_path) {
            Storage::disk($component->datasheet_disk)->delete($component->datasheet_path);
        }

        if ($component->image_path) {
            Storage::disk($component->image_disk)->delete($component->image_path);
        }

        $this->components->delete($component);
    }

    /**
     * @return array<string, mixed>
     */
    private function baseAttributes(ComponentData $data): array
    {
        return [
            'component_category_id' => $data->categoryId,
            'name' => trim($data->name),
            'manufacturer' => $this->nullableTrimmedValue($data->manufacturer),
            'reference' => $this->nullableTrimmedValue($data->reference),
            'description' => $this->nullableTrimmedValue($data->description),
            'specs' => $data->specs,
            'price_cents' => $data->priceCents,
            'currency' => $data->currency,
            'supplier_url' => $this->nullableTrimmedValue($data->supplierUrl),
            'image_url' => $this->nullableTrimmedValue($data->imageUrl),
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function persistWithFiles(?Component $existing, array $attributes, ComponentData $data): Component
    {
        $disk = config('roboforge.resources.disk');
        $ulid = $existing?->getKey() ?? (string) Str::ulid();
        $stored = [];
        $previousFiles = [
            'datasheet' => ['path' => $existing?->datasheet_path, 'disk' => $existing?->datasheet_disk],
            'image' => ['path' => $existing?->image_path, 'disk' => $existing?->image_disk],
        ];

        if ($data->datasheet !== null) {
            $this->validateDatasheet($data->datasheet);
            $stored['datasheet'] = $this->storeUploadedFile($data->datasheet, $disk, "components/{$ulid}/datasheet");
        }

        if ($data->image !== null) {
            $this->validateImage($data->image);
            $stored['image'] = $this->storeUploadedFile($data->image, $disk, "components/{$ulid}/image");
        }

        foreach ($stored as $field => $file) {
            $attributes["{$field}_disk"] = $disk;
            $attributes["{$field}_path"] = $file['path'];
            $attributes["{$field}_original_name"] = $file['original_name'];
        }

        if ($existing === null) {
            $attributes['id'] = $ulid;
        }

        try {
            $component = DB::transaction(fn (): Component => $existing
                ? $this->components->update($existing, $attributes)
                : $this->components->create($attributes));
        } catch (Throwable $e) {
            foreach ($stored as $file) {
                Storage::disk($disk)->delete($file['path']);
            }

            throw $e;
        }

        foreach ($stored as $field => $file) {
            $previousPath = $previousFiles[$field]['path'];
            $previousDisk = $previousFiles[$field]['disk'];

            if ($previousPath && $previousPath !== $file['path']) {
                Storage::disk($previousDisk)->delete($previousPath);
            }
        }

        return $component;
    }

    private function validateDatasheet(UploadedFile $file): void
    {
        $extension = mb_strtolower($file->getClientOriginalExtension());

        if ($extension !== 'pdf' || $file->getMimeType() !== 'application/pdf') {
            throw ValidationException::withMessages([
                'datasheet' => ['Seuls les fichiers PDF sont acceptés pour la fiche technique.'],
            ]);
        }
    }

    private function validateImage(UploadedFile $file): void
    {
        $extension = mb_strtolower($file->getClientOriginalExtension());
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (! in_array($extension, $allowedExtensions, true) || ! str_starts_with($file->getMimeType() ?? '', 'image/')) {
            throw ValidationException::withMessages([
                'image' => ['Seuls les fichiers image (jpg, png, webp, gif) sont acceptés.'],
            ]);
        }
    }

    /**
     * @return array{path: string, original_name: string}
     */
    private function storeUploadedFile(UploadedFile $file, string $disk, string $directory): array
    {
        $originalName = $this->sanitizeFileName($file->getClientOriginalName());
        $storedPath = $file->storeAs($directory, $originalName, ['disk' => $disk]);

        return ['path' => $storedPath, 'original_name' => $originalName];
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

    private function nullableTrimmedValue(?string $value): ?string
    {
        $value = $value === null ? null : trim($value);

        return $value === '' ? null : $value;
    }
}
