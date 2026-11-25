<?php

namespace Tests\Acceptance\Movements;

use App\Models\Pallet;
use App\Models\User;
use PHPUnit\Framework\Assert;
use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class MovementCest extends BaseAcceptanceCest
{
    public function createMovementWithValidData(AcceptanceTester $page): void
    {
        $this->loginAsAdmin($page);

        $pallet = $this->createPallet('PL003', '900', '820');
        $this->registerMovement($page, $pallet->id, '10');

        $page->see('Movimentação registrada com sucesso.');
        $page->seeCurrentUrlEquals('/movements');

        $updatedPallet = Pallet::findById($pallet->id);

        Assert::assertEquals(15, $updatedPallet->amount);
    }

    public function movementAppearsInHistory(AcceptanceTester $page): void
    {
        $this->loginAsAdmin($page);

        $pallet = $this->createPallet('PL010', '80', '120');
        $this->registerMovement($page, $pallet->id, '8');

        $page->amOnPage('/movements/history');

        $page->see('Entrada');
        $page->see('PL010');
        $page->see('8');
    }

    public function removingARelationRevertsPalletAmount(AcceptanceTester $page): void
    {
        $this->loginAsAdmin($page);

        $pallet = $this->createPallet('PL011', '80', '120');
        $this->registerMovement($page, $pallet->id, '8');

        $page->amOnPage('/movements/history');
        $page->see('PL011');

        $page->submitForm("form[action*='pallet-movements']", []);

        $page->see('Movimentação excluida com sucesso.');

        $updatedPallet = Pallet::findById($pallet->id);
        Assert::assertEquals(5, $updatedPallet->amount);
    }

    public function cannotDeletePalletThatHasMovements(AcceptanceTester $page): void
    {
        $this->loginAsAdmin($page);

        $pallet = $this->createPallet('PL012', '80', '120');
        $this->registerMovement($page, $pallet->id, '8');

        $page->amOnPage('/pallets');
        $page->submitForm("form[action='/pallets/{$pallet->id}']", []);

        $page->see('Não foi possivel excluir pallet, pois existe movimentações no histórico do mesmo');

        Assert::assertNotNull(Pallet::findById($pallet->id));
    }

    private function registerMovement(AcceptanceTester $page, int $palletId, string $quantity): void
    {
        $page->amOnPage('/movements');
        $page->selectOption('movement[type]', 'inbound');
        $page->selectOption('pallet_movements[0][pallet_id]', (string) $palletId);
        $page->fillField('pallet_movements[0][quantity]', $quantity);
        $page->executeJS("document.querySelector('#movement-form button[type=\"submit\"]').click();");
        $page->wait(1);
    }


    private function loginAsAdmin(AcceptanceTester $page): void
    {
        $admin = new User([
            'name' => 'Acceptance Admin',
            'email' => 'acceptance-admin@example.com',
            'role' => 'admin',
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);
        $admin->save();

        $page->login($admin->email, '123456');
        $page->seeCurrentUrlEquals('/');
    }

    private function createPallet(
        string $item,
        string $width,
        string $length
    ): Pallet {
        $pallet = new Pallet([
            'item' => $item,
            'width' => $width,
            'length' => $length,
            'location' => 'Acceptance test',
            'amount' => 5,
            'minimum_amount' => 1,
        ]);
        $pallet->save();

        return $pallet;
    }
}
