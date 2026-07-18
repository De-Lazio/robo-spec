<?php

namespace App\Domain\Components\Services;

use App\Domain\Components\Contracts\ComponentCategoryRepositoryInterface;
use App\Domain\Components\Contracts\ComponentRepositoryInterface;
use App\Domain\Components\DTOs\ComponentData;
use App\Domain\Components\DTOs\CreateComponentCategoryData;
use App\Models\Component;
use App\Models\ComponentCategory;
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

        if ($data->datasheet === null) {
            return $this->components->create($attributes);
        }

        return $this->storeWithDatasheet($attributes, $data->datasheet, null);
    }

    public function updateComponent(Component $component, ComponentData $data): Component
    {
        $attributes = $this->baseAttributes($data);

        if ($data->datasheet === null) {
            return $this->components->update($component, $attributes);
        }

        return $this->storeWithDatasheet($attributes, $data->datasheet, $component);
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
        if ($component->datasheet_path) {
            Storage::disk($component->datasheet_disk)->delete($component->datasheet_path);
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
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function storeWithDatasheet(array $attributes, UploadedFile $file, ?Component $existing): Component
    {
        $extension = mb_strtolower($file->getClientOriginalExtension());

        if ($extension !== 'pdf' || $file->getMimeType() !== 'application/pdf') {
            throw ValidationException::withMessages([
                'datasheet' => ['Seuls les fichiers PDF sont acceptés pour la fiche technique.'],
            ]);
        }

        $disk = config('roboforge.resources.disk');
        $ulid = $existing?->getKey() ?? (string) Str::ulid();
        $originalName = $this->sanitizeFileName($file->getClientOriginalName());
        $directory = "components/{$ulid}/datasheet";
        $previousDisk = $existing?->datasheet_disk;
        $previousPath = $existing?->datasheet_path;

        $storedPath = $file->storeAs($directory, $originalName, ['disk' => $disk]);

        $attributes = array_merge($attributes, [
            'datasheet_disk' => $disk,
            'datasheet_path' => $storedPath,
            'datasheet_original_name' => $originalName,
        ]);

        if ($existing === null) {
            $attributes['id'] = $ulid;
        }

        try {
            $component = DB::transaction(fn (): Component => $existing
                ? $this->components->update($existing, $attributes)
                : $this->components->create($attributes));
        } catch (Throwable $e) {
            Storage::disk($disk)->delete($storedPath);

            throw $e;
        }

        if ($previousPath && $previousPath !== $storedPath) {
            Storage::disk($previousDisk)->delete($previousPath);
        }

        return $component;
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
