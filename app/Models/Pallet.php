<?php

namespace App\Models;

use Lib\Validations;
use Core\Database\ActiveRecord\Model;
use Core\Database\ActiveRecord\HasMany;


/**
 * @property int $id
 * @property string $item
 * @property string $width
 * @property string $length
 * @property string $location
 * @property string $amount
 * @property string $minimum_amount
 */

class Pallet extends Model
{
    protected static string $table = 'pallets';
    protected static array $columns = ['item', 'width', 'length', 'location', 'amount', 'minimum_amount'];


    public function validates(): void
    {
        $validateToConvert = [];

        Validations::isPositive('width',$this);
        Validations::isPositive('length',$this);
        Validations::isNumber('width',$this);
        Validations::isNumber('length',$this);
        Validations::notEmpty('width', $this);
        Validations::notEmpty('length', $this);
        Validations::notEmpty('item', $this);
        Validations::uniqueness('item', $this);
        Validations::uniqueness(['length', 'width'], $this);
        $validateToConvert[] = Validations::isNumber('amount',$this);
        $validateToConvert[] = Validations::isNumber('minimum_amount', $this);
        $validateToConvert[] = Validations::isPositive('amount',$this);
        $validateToConvert[] = Validations::isPositive('minimum_amount',$this);
        $validateToConvert[] = Validations::isInteger('amount', $this);
        $validateToConvert[] = Validations::isInteger('minimum_amount', $this);
        $validateToConvert[] = Validations::isMaxLarger('minimum_amount','amount', $this);

        if(!in_array(false, $validateToConvert)) {
            $this->convertToInt('amount');
            $this->convertToInt('minimum_amount');
        } 
    }

    public function existPallet(string $length, string $width): bool
    {
        if (Pallet::findBy(['length' => $length, 'width' => $width])) {
                return true;
        }

        return false;
    }

    public function convertToInt(string $attribute): void
    {
        $this->$attribute = (int) $this->$attribute;
    }

    public function photos():HasMany
    {
      return  $this->hasMany(Photo::class,'pallet_id');
    }
}
