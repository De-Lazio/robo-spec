<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Component extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'component_category_id',
        'owner_project_id',
        'name',
        'manufacturer',
        'reference',
        'description',
        'specs',
        'datasheet_disk',
        'datasheet_path',
        'datasheet_original_name',
        'price_cents',
        'currency',
        'supplier_url',
        'image_url',
        'image_disk',
        'image_path',
        'image_original_name',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'specs' => 'array',
            'is_active' => 'boolean',
            'price_cents' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ComponentCategory::class, 'component_category_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ownerProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'owner_project_id');
    }

    public function projectComponents(): HasMany
    {
        return $this->hasMany(ProjectComponent::class);
    }
}
