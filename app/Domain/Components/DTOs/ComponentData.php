<?php

namespace App\Domain\Components\DTOs;

use Illuminate\Http\UploadedFile;

readonly class ComponentData
{
    /**
     * @param  array<string, string>  $specs
     */
    public function __construct(
        public int $categoryId,
        public string $name,
        public ?string $manufacturer,
        public ?string $reference,
        public ?string $description,
        public array $specs,
        public ?UploadedFile $datasheet,
        public ?int $priceCents,
        public ?string $currency,
        public ?string $supplierUrl,
        public ?string $imageUrl = null,
        public ?UploadedFile $image = null,
    ) {}
}
