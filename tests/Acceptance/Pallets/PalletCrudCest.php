<?php

namespace Tests\Acceptance\Pallets;

use App\Models\Pallet;
use App\Models\User;
use PHPUnit\Framework\Assert;
use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class PalletCrudCest extends BaseAcceptanceCest
{
    public function createPalletWithInvalidData(AcceptanceTester $page): void
    {
        $this->loginAsAdmin($page);
        $page->amOnPage('/pallets/new');

        $page->click('Cadastrar');

        $page->see('não pode ser vazio!');
        $page->seeCurrentUrlEquals('/pallets');
        Assert::assertSame(0, Pallet::paginate(1, 10)->totalOfRegisters());
    }

    public function createPalletSuccessfully(AcceptanceTester $page): void
    {
        $this->loginAsAdmin($page);
        $page->amOnPage('/pallets/new');
        $this->fillPalletForm($page, 'Acceptance pallet', '80', '120', '15', '3');

        $page->click('Cadastrar');

        $page->see('Pallet registrado com sucesso!');
        $page->seeCurrentUrlEquals('/');
        $page->amOnPage('/pallets');
        $page->see('Acceptance pallet');
        Assert::assertNotNull(Pallet::findBy(['item' => 'Acceptance pallet']));
    }

    public function updatePalletWithInvalidData(AcceptanceTester $page): void
    {
        $pallet = $this->createPallet('Original pallet', '80', '120');
        $this->createPallet('Already used item', '90', '130');
        $this->loginAsAdmin($page);
        $page->amOnPage("/pallets/{$pallet->id}/edit");
        $page->fillField('pallet[item]', 'Already used item');

        $page->submitForm(
            "form[action='/pallets/{$pallet->id}']",
            ['pallet[item]' => 'Already used item']
        );

        $page->see('já existe um registro com esse dado');
        Assert::assertSame('Original pallet', Pallet::findById($pallet->id)->item);
    }

    public function updatePalletSuccessfully(AcceptanceTester $page): void
    {
        $pallet = $this->createPallet('Update target', '80', '120');
        $this->loginAsAdmin($page);
        $page->amOnPage("/pallets/{$pallet->id}/edit");
        $page->fillField('pallet[amount]', '18');

        $page->submitForm(
            "form[action='/pallets/{$pallet->id}']",
            ['pallet[amount]' => '18']
        );

        $page->see('Pallet atualizado');
        $page->seeCurrentUrlEquals('/pallets');
        Assert::assertSame(18, (int) Pallet::findById($pallet->id)->amount);
    }

    public function listAndPaginatePallets(AcceptanceTester $page): void
    {
        for ($index = 1; $index <= 12; $index++) {
            $this->createPallet(
                sprintf('Pagination pallet %02d', $index),
                (string) (70 + $index),
                (string) (110 + $index)
            );
        }
        $this->loginAsAdmin($page);
        $page->amOnPage('/pallets');

        $page->see('Mostrando 1 - 10 de 12');
        $page->see('Pagination pallet 01');
        $page->see('Pagination pallet 10');
        $page->dontSee('Pagination pallet 11');

        $page->amOnPage('/pallets?page=2');

        $page->see('Mostrando 11 - 12 de 12');
        $page->see('Pagination pallet 11');
        $page->see('Pagination pallet 12');
        $page->dontSee('Pagination pallet 01');
    }

    public function deletePalletSuccessfully(AcceptanceTester $page): void
    {
        $pallet = $this->createPallet('Pallet to delete', '80', '120');
        $this->loginAsAdmin($page);
        $page->amOnPage('/pallets');
        $page->submitForm("form[action='/pallets/{$pallet->id}']", []);

        $page->see('Pallet Excluido');
        $page->dontSee('Pallet to delete');
        Assert::assertNull(Pallet::findById($pallet->id));
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

    private function fillPalletForm(
        AcceptanceTester $page,
        string $item,
        string $width,
        string $length,
        string $amount,
        string $minimumAmount
    ): void {
        $page->fillField('pallet[item]', $item);
        $page->fillField('pallet[width]', $width);
        $page->fillField('pallet[length]', $length);
        $page->fillField('pallet[amount]', $amount);
        $page->fillField('pallet[minimum_amount]', $minimumAmount);
    }
}
