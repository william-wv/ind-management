<?php

namespace App\Controllers;

use App\Models\User;
use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\Authentication\Auth;
use Lib\FlashMessage;

class AuthenticationsController extends Controller
{
    protected string $layout = 'login';

    public function new(): void
    {
        $this->render('authentications/new');
    }

    public function authenticate(Request $request): void
    {
        $params = $request->getParam('user');
        $user = User::findByEmail($params['email']);

        if ($user && $user->authenticate($params['password'])) {
            Auth::login($user);
            FlashMessage::success('Login realizado com sucesso!');

            if ($request->acceptJson()) {
                $this->renderJson('authentications/login_result', [
                    'success' => true,
                    'redirect' => route('root'),
                ]);
                return;
            }

            $this->redirectTo(route('root'));
        } else {
            if ($request->acceptJson()) {
                $this->renderJson('authentications/login_result', [
                    'success' => false,
                    'message' => 'Email e/ou senha inválidos!',
                ]);
                return;
            }

            FlashMessage::danger('Email e/ou senha inválidos!');
            $this->redirectTo(route('users.login'));
        }
    }

    public function destroy(): void
    {
        Auth::logout();
        FlashMessage::success('Logout realizado com sucesso!');
        $this->redirectTo(route('users.login'));
    }
}
