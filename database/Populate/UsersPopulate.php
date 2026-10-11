<?php

namespace Database\Populate;

use App\Models\User;

class UsersPopulate
{
    public static function populate()
    {
        $data =  [
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'password' => 'admin',
            'password_confirmation' => 'admin'
        ];

        $user = new User($data);
        $user->save();

        $numberOfUsers = 2;

        for ($i = 1; $i < $numberOfUsers; $i++) {
            $data =  [
                'name' => 'User ' . $i,
                'email' => 'basic' . $i . '@example.com',
                'password' => 'basic',
                'password_confirmation' => 'basic'
            ];

            $user = new User($data);
            $user->save();
        }

        echo "Users populated with $numberOfUsers registers\n";
    }
}
