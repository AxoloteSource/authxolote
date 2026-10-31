<?php

namespace App\Models;

use Database\Factories\UserListFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserList extends Model
{
    public const string SLUG_ALL_USERS = 'all-users';

    /** @use HasFactory<UserListFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function isAllUsers(): bool
    {
        return $this->slug === self::SLUG_ALL_USERS;
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_list_user')
            ->withTimestamps();
    }
}
