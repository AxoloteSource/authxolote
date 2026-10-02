<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActionRoleOverride extends Model
{
    use HasFactory;

    protected $fillable = ['action_id', 'role_id', 'active'];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function action(): BelongsTo
    {
        return $this->belongsTo(Action::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
