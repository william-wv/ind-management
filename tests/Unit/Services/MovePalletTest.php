<?php

namespace Tests\Unit\Services;

use App\Models\Pallet;
use App\Models\PalletMovement;
use App\Models\User;
use App\Services\MovePallet;
use Tests\TestCase;

class MovePalletTest extends TestCase
{
    public function test_inbound_movement_increases_pallet_amount(): void
    {
        $pallet = $this->createPallet('pl-inbound', 5);
        $user = $this->createUser();

        $data = [
            'type' => 'inbound',
            'observation' => 'nada',
            'user_id' => $user->id,
            'pallets' => [['pallet_id' => $pallet->id, 'quantity' => 5]],
        ];

        $movement = (new MovePallet())->create($data);

        $this->assertFalse($movement->hasErrors());

        $pallet = Pallet::findById($pallet->id);
        $this->assertEquals(10, $pallet->amount);
    }

    public function test_outbound_movement_decreases_pallet_amount(): void
    {
        $pallet = $this->createPallet('pl-outbound', 10);
        $user = $this->createUser();

        $data = [
            'type' => 'outbound',
            'observation' => 'nada',
            'user_id' => $user->id,
            'pallets' => [['pallet_id' => $pallet->id, 'quantity' => 4]],
        ];

        $movement = (new MovePallet())->create($data);

        $this->assertFalse($movement->hasErrors());

        $pallet = Pallet::findById($pallet->id);
        $this->assertEquals(6, $pallet->amount);
    }

    public function test_outbound_movement_with_insufficient_stock_fails_and_rolls_back(): void
    {
        $pallet = $this->createPallet('pl-insufficient', 3);
        $user = $this->createUser();

        $data = [
            'type' => 'outbound',
            'observation' => 'nada',
            'user_id' => $user->id,
            'pallets' => [['pallet_id' => $pallet->id, 'quantity' => 10]],
        ];

        $movement = (new MovePallet())->create($data);

        $this->assertTrue($movement->hasErrors());

        $pallet = Pallet::findById($pallet->id);
        $this->assertEquals(3, $pallet->amount);
    }

    public function test_inventory_movement_sets_exact_amount(): void
    {
        $pallet = $this->createPallet('pl-inventory', 20);
        $user = $this->createUser();

        $data = [
            'type' => 'inventory',
            'observation' => 'nada',
            'user_id' => $user->id,
            'pallets' => [['pallet_id' => $pallet->id, 'quantity' => 7]],
        ];

        $movement = (new MovePallet())->create($data);

        $this->assertFalse($movement->hasErrors());

        $pallet = Pallet::findById($pallet->id);
        $this->assertEquals(7, $pallet->amount);
    }

    public function test_movement_with_nonexistent_pallet_fails(): void
    {
        $user = $this->createUser();

        $data = [
            'type' => 'inbound',
            'observation' => 'nada',
            'user_id' => $user->id,
            'pallets' => [['pallet_id' => 999999, 'quantity' => 5]],
        ];

        $movement = (new MovePallet())->create($data);

        $this->assertTrue($movement->hasErrors());
    }

    public function test_movement_with_invalid_type_fails(): void
    {
        $pallet = $this->createPallet('pl-invalid-type', 5);
        $user = $this->createUser();

        $data = [
            'type' => 'sabotagem',
            'observation' => 'nada',
            'user_id' => $user->id,
            'pallets' => [['pallet_id' => $pallet->id, 'quantity' => 5]],
        ];

        $movement = (new MovePallet())->create($data);

        $this->assertTrue($movement->hasErrors());
        $this->assertNotNull($movement->errors('type'));
    }

    public function test_movement_without_pallets_fails(): void
    {
        $user = $this->createUser();

        $data = [
            'type' => 'inbound',
            'observation' => 'nada',
            'user_id' => $user->id,
            'pallets' => [],
        ];

        $movement = (new MovePallet())->create($data);

        $this->assertTrue($movement->hasErrors());
        $this->assertNotNull($movement->errors('pallet_movements'));
    }

    public function test_destroy_reverts_pallet_amount_to_quantity_before(): void
    {
        $pallet = $this->createPallet('pl-destroy', 5);
        $user = $this->createUser();

        $data = [
            'type' => 'inbound',
            'observation' => 'nada',
            'user_id' => $user->id,
            'pallets' => [['pallet_id' => $pallet->id, 'quantity' => 5]],
        ];

        $movement = (new MovePallet())->create($data);
        $this->assertFalse($movement->hasErrors());

        /** @var PalletMovement $palletMovement */
        $palletMovement = $movement->palletMovements()->get()[0];

        $destroyed = (new MovePallet())->destroy($palletMovement);

        $this->assertFalse($destroyed->hasErrors());

        $pallet = Pallet::findById($pallet->id);
        $this->assertEquals(5, $pallet->amount);
    }

    private function createPallet(string $item, int $amount): Pallet
    {
        $pallet = new Pallet([
            'item' => $item,
            'width' => '100',
            'length' => '80',
            'location' => 'Unit test',
            'amount' => $amount,
            'minimum_amount' => 1,
        ]);

        $this->assertTrue($pallet->save());

        return $pallet;
    }

    private function createUser(): User
    {
        $user = new User([
            'name' => 'Marcio',
            'email' => 'email@gmail.com',
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);

        $this->assertTrue($user->save());

        return $user;
    }
}
