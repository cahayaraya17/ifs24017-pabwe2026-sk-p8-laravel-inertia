<?php

namespace App\Features\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RegisterUserAction
{
    public function __invoke(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'member',
        ]);

        Auth::login($user);

        return $user;
    }
}