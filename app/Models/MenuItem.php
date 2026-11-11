<?php

namespace App\Models;

use App\Enums\MenuItemType;
use App\Enums\RoleEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItem extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'menu_id',
        'parent_id',
        'type',
        'name',
        'slug',
        'route',
        'path',
        'icon',
        'sort_order',
        'active',
    ];

    protected $casts = [
        'type' => MenuItemType::class,
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')
            ->orderBy('sort_order');
    }

    public function isHeader(): bool
    {
        return $this->type?->isHeader() ?? false;
    }

    public function isLink(): bool
    {
        return $this->type?->isLink() ?? false;
    }

    public function isSection(): bool
    {
        return $this->type?->isSection() ?? false;
    }

    public function isVisibleTo(?string $roleId): bool
    {
        if ($roleId === RoleEnum::Root->value) {
            return true;
        }

        if ($roleId === null) {
            return false;
        }

        if ($this->relationLoaded('roles')) {
            return $this->roles->contains('id', $roleId);
        }

        return $this->roles()->where('roles.id', $roleId)->exists();
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    public static function findBySlugAndMenu(string $slug, string $menuId, ?string $parentId = null): ?static
    {
        return static::where('slug', $slug)
            ->where('menu_id', $menuId)
            ->where('parent_id', $parentId)
            ->first();
    }
}
