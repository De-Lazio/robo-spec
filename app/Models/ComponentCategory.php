<?php

namespace App\Models;

use App\Domain\Components\Enums\ComponentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComponentCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'slug',
    ];

    protected function casts(): array
    {
        return [
            'type' => ComponentType::class,
        ];
    }

    public function components(): HasMany
    {
        return $this->hasMany(Component::class);
    }
}
