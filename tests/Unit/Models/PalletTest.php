<?php

namespace Tests\Unit\Models;

use App\Models\Pallet;
use Tests\TestCase;

class PalletTest extends TestCase
{
    public function test_required_fields_must_be_present(): void
    {
        $pallet = new Pallet();

        $this->assertFalse($pallet->isValid());
        $this->assertSame('não pode ser vazio!', $pallet->errors('width'));
        $this->assertSame('não pode ser vazio!', $pallet->errors('length'));
        $this->assertSame('não pode ser vazio!', $pallet->errors('item'));
    }

    public function test_valid_pallet_passes_validation(): void
    {
        $pallet = $this->buildPallet('Valid pallet', '80', '120');

        $this->assertTrue($pallet->isValid());
    }

    public function test_item_must_be_unique(): void
    {
        $this->buildPallet('Unique item', '80', '120')->save();
        $duplicate = $this->buildPallet('Unique item', '90', '130');

        $this->assertFalse($duplicate->isValid());
        $this->assertSame(
            'já existe um registro com esse dado',
            $duplicate->errors('item')
        );
    }

    public function test_pallet_can_keep_its_own_item_when_updated(): void
    {
        $pallet = $this->buildPallet('Existing item', '80', '120');
        $pallet->save();
        $pallet->amount = '10';

        $this->assertTrue($pallet->isValid());
    }

    public function test_exist_pallet_matches_dimensions(): void
    {
        $pallet = $this->buildPallet('Dimensions', '80', '120');
        $pallet->save();

        $this->assertTrue($pallet->existPallet('120', '80'));
        $this->assertFalse($pallet->existPallet('130', '90'));
    }

    private function buildPallet(string $item, string $width, string $length): Pallet
    {
        return new Pallet([
            'item' => $item,
            'width' => $width,
            'length' => $length,
            'location' => 'Unit test',
            'amount' => 5,
            'minimum_amount' => 1,
        ]);
    }
}
