<?php

namespace App\Models;

use App\Domain\Resources\Enums\ResourceCategory;
use App\Domain\Resources\Enums\ResourceKind;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resource extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'id',
        'project_id',
        'uploaded_by',
        'category',
        'kind',
        'name',
        'original_name',
        'disk',
        'path',
        'mime_type',
        'size_bytes',
        'description',
        'checksum',
    ];

    protected function casts(): array
    {
        return [
            'category' => ResourceCategory::class,
            'kind' => ResourceKind::class,
            'size_bytes' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
