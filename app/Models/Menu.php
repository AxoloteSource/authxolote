<?php

namespace App\Models;

use App\Enums\MenuSize;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'application_id',
        'name',
        'slug',
        'icon',
        'size',
        'sort_order',
        'active',
    ];

    protected $casts = [
        'size' => MenuSize::class,
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public static function findBySlugAndApplication(string $slug): ?static
    {
        return static::where('slug', $slug)->first();
    }
}
