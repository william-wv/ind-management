<?php 

namespace App\Models;

use Models\Movement;
use Core\Database\ActiveRecord\BelongsTo;
use Core\Database\ActiveRecord\Model;
use Models\Pallet;

class PalletMovement extends Model{

/**
 * @property int $id
 * @property string $type
 * @property string $observation
 */
    
    protected static string $table = 'pallet_movements';
    protected static array $columns = ['movement_id', 'pallet_id', 'quantity', 'quantity_before', 'quantity_after'];

    public function pallet():BelongsTo{
        return $this->BelongsTo(Pallet::class, 'pallet_id');
    }

    public function movement(): BelongsTo
    {
        return $this->belongsTo(Movement::class, 'movement_id');
    }

    
}