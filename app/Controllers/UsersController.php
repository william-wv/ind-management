<?php

namespace App\Controllers;

use App\Models\User;
use Core\Http\Controllers\Controller;

class UsersController extends Controller
{
    public function index(): void
    {
        $title = 'Usuários';
        $users = User::all();

        $this->render('users/index', compact('title', 'users'));
    }
}
