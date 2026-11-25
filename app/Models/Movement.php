<?php

namespace App\Models;

use Core\Database\ActiveRecord\BelongsTo;
use Core\Database\ActiveRecord\BelongsToMany;
use Core\Database\ActiveRecord\HasMany;
use Core\Database\ActiveRecord\Model;

/**
 * @property int $id
 * @property string $type
 * @property string $observation
 * @property int $user_id
 */
class Movement extends Model
{
    protected static string $table = 'movements';
    protected static array $columns = ['type', 'user_id', 'observation'];

    public function pallets(): BelongsToMany
    {
        return $this->BelongsToMany(Pallet::class, 'pallet_movements', 'movement_id', 'pallet_id');
    }

    public function palletMovements(): HasMany
    {
        return $this->hasMany(PalletMovement::class, 'movement_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
