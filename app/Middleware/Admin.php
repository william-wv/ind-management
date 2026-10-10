<?php

namespace App\Middleware;

use Core\Http\Middleware\Middleware;
use Core\Http\Request;
use Lib\Authentication\Auth;
use Lib\FlashMessage;

class Admin implements Middleware
{
    public function handle(Request $request): void
    {
        // Grupo 'admin' aninhado dentro de 'auth' roda ANTES do 'auth'
        // (RouteWrapperMiddleware adiciona o do grupo interno primeiro).
        (new Authenticate())->handle($request);

        if (!Auth::user()->isAdmin()) {
            FlashMessage::danger('Você não tem permissão para acessar essa página');
            $this->redirectTo(route('root'));
        }
    }

    private function redirectTo(string $location): void
    {
        header('Location: ' . $location);
        exit;
    }
}
