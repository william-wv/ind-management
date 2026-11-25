<?php

namespace App\Models;

use Core\Database\ActiveRecord\BelongsTo;
use Core\Database\ActiveRecord\Model;

/**
 * @property int $id
 * @property int $movement_id
 * @property int $pallet_id
 * @property int $quantity
 * @property int $quantity_before
 * @property int $quantity_after
 */
class PalletMovement extends Model
{
    protected static string $table = 'pallet_movements';
    protected static array $columns = ['movement_id', 'pallet_id', 'quantity', 'quantity_before', 'quantity_after'];

    public function pallet(): BelongsTo
    {
        return $this->BelongsTo(Pallet::class, 'pallet_id');
    }

    public function movement(): BelongsTo
    {
        return $this->belongsTo(Movement::class, 'movement_id');
    }
}
