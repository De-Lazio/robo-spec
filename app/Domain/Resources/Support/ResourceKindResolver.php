<?php

namespace App\Domain\Resources\Support;

use App\Domain\Resources\Enums\ResourceKind;
use App\Models\Resource;

class ResourceKindResolver
{
    /**
     * CAD extensions that a 3D model viewer can render client-side. Every
     * other CAD/schema extension (STEP, IGES, SolidWorks native, KiCad,
     * Eagle...) stays download-only — no lightweight JS library renders
     * them faithfully.
     *
     * @var list<string>
     */
    public const PREVIEWABLE_MODEL_EXTENSIONS = ['stl', 'gltf', 'glb'];

    public static function isPreviewable(Resource $resource): bool
    {
        if (in_array($resource->kind, [ResourceKind::Image, ResourceKind::Document], true)) {
            return true;
        }

        if ($resource->kind !== ResourceKind::Cad) {
            return false;
        }

        $extension = mb_strtolower(pathinfo($resource->original_name, PATHINFO_EXTENSION));

        return in_array($extension, self::PREVIEWABLE_MODEL_EXTENSIONS, true);
    }

    public static function previewKind(Resource $resource): ?string
    {
        return match (true) {
            $resource->kind === ResourceKind::Image => 'image',
            $resource->kind === ResourceKind::Document => 'document',
            self::isPreviewable($resource) => 'model',
            default => null,
        };
    }

    public static function fromExtension(string $extension): ?ResourceKind
    {
        $extension = mb_strtolower($extension);

        foreach (config('roboforge.resources.allowed_extensions', []) as $kind => $extensions) {
            if (in_array($extension, $extensions, true)) {
                return ResourceKind::from($kind);
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public static function allowedExtensions(): array
    {
        return collect(config('roboforge.resources.allowed_extensions', []))
            ->flatten()
            ->unique()
            ->values()
            ->all();
    }
}
