<?php

namespace App\Models;

use Core\Database\ActiveRecord\Model;
use Lib\Validations;
use Core\Database\ActiveRecord\BelongsTo;

/**
 * @property int $id
 * @property string $url
 * @property string $originaç_name
 * @property string $mime_type
 * @property int $size_bytes
 */

class Photo extends Model
{

    protected static string $table = 'pallet_sell_orders_photos';
    protected static array $columns = ['pallet_id', 'url', 'original_name', 'mime_type', 'size_bytes'];
    

    public function validates():void
    {
        Validations::notEmpty('url',$this);
        Validations::notEmpty('original_name',$this);
        Validations::notEmpty('mime_type', $this);
        Validations::notEmpty('size_bytes',$this);
        Validations::notEmpty('pallet_id',$this);
    }

    public function pallet():BelongsTo
    {
        return $this->belongsTo(Pallet::class,'pallet_id');
    }


}

