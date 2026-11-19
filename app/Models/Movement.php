<?php 

namespace App\Models;

use Core\Database\ActiveRecord\BelongsTo;
use Core\Database\ActiveRecord\BelongsToMany;
use Core\Database\ActiveRecord\HasMany;
use Core\Database\ActiveRecord\Model;

class Movement extends Model{

/**
 * @property int $id
 * @property string $type
 * @property string $observation
 */
    
    protected static string $table = 'movements';
    protected static array $columns = ['type', 'user_id', 'observation'];

    public function pallets():BelongsToMany{
        return $this->BelongsToMany(Pallet::class, 'pallet_movements', 'movement_id','pallet_id');
    }

    public function palletMovements():HasMany{
        return $this->hasMany(PalletMovement::class, 'movement_id');
    }

}