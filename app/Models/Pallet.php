<?php
namespace App\Models;
use Lib\Validations;
use Core\Database\ActiveRecord\Model;

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
        Validations::notEmpty('item', $this);
        Validations::notEmpty('width', $this);
        Validations::notEmpty('length', $this);
        Validations::uniqueness('item',$this);

        
    }

    public function existPallet(string $length, string $width ):bool
    {
        if(Pallet::findBy(['length' => $length, 'width' => $width])){
                return true;
        }

        return false;
    }

    public function convertToInt(string $attribute):void{
        $this->$attribute = (int) $this->$attribute;    
    }


    




}