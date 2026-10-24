<?php

use Lib\Validations;
use Core\Database\ActiveRecord\Model;

class Pallet extends Model
{
    private string $id;
    private string $item;
    private int $width;
    private int $length;
    private string $location;
    private int $amount;

    
    public function __construct(string $id, string $item, int $width, int $length, string $location, int $amount)
    {
        $this->id = $id;
        $this->item = $item;
        $this->width = $width;
        $this->length = $length;
        $this->location = $location;
        $this->amount = $amount;
    }

    public function getId(): string
    {
        return $this->id;
    }


    public function getItem(): string
    {
        return $this->item;
    }

    public function getWidth(): int
    {
        return $this->width;
    }
    
    public function getLength(): int
    {
        return $this->length;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }


    public validates(): void
    {
        Validations::notEmpty('item', $this);
        Validations::notEmpty('width', $this);
        Validations::notEmpty('length', $this);
        Validations::notEmpty('amount',$this);
        Validations::uniqueness('item',$this);
    }


    




}