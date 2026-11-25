<?php

declare(strict_types=1);

namespace Tests\Support\Helper;

use Codeception\Module;

class LoginHelper extends Module
{
    public function login(string $username, string $password): void
    {
        /** @var \Tests\Support\AcceptanceTester $page */
        $page = $this->getModule('WebDriver');
        $page->amOnPage('/login');
        $page->fillField('user[email]', $username);
        $page->fillField('user[password]', $password);
        $page->click('Entrar');

        // O login agora é feito via Ajax (fetch assíncrono): o clique não
        // bloqueia a navegação, então precisamos esperar o JS terminar
        // (receber a resposta e navegar ou exibir o erro) antes de seguir.
        $page->wait(1);
    }

    public function logout(): void
    {
        /** @var \Tests\Support\AcceptanceTester $page */
        $page = $this->getModule('WebDriver');
        $page->click('fulano@example.com');
        $page->click('Sair');
    }
}
