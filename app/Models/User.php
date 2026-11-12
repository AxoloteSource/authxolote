<?php

namespace App\Models;

use App\Enums\RoleEnum;
use App\Enums\UserTypeEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'role_id',
        'user_type_id',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $attributes = [
        'user_type_id' => UserTypeEnum::Human->value,
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'banned_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function userType(): BelongsTo
    {
        return $this->belongsTo(UserType::class);
    }

    public function isBanned(): bool
    {
        return ! is_null($this->banned_at);
    }

    public function otps(): HasMany
    {
        return $this->hasMany(Otp::class);
    }

    public function isRoot(): bool
    {
        return $this->role->id == RoleEnum::Root;
    }

    public function belongsToAction(string $action): bool
    {
        if ($this->role_id != RoleEnum::Root->value
            && ! $this->role->actions()->where('name', $action)->exists()
        ) {
            return false;
        }

        return true;
    }

    public function attachAction(string|array $action): void
    {
        $actions = is_array($action) ? $action : [$action];

        foreach ($actions as $action) {
            $actionInstance = Action::firstOrCreate(['name' => $action], ['description' => '']);

            if (! $this->role->actions()->where('name', $action)->exists()) {
                $this->role->actions()->attach($actionInstance->id);
            }
        }
    }

    public function scopeSearchByEmail($query, $email): void
    {
        $query->where('email', $email);
    }
}
