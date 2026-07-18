<?php

namespace App\Domain\Resources\Support;

use App\Domain\Resources\Enums\ResourceKind;

class ResourceKindResolver
{
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
