<?php

namespace App\Actions\Admin;

use App\Models\User;
use App\Support\AdminAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CreateAdmin
{
    public function handle(string $name, string $email, #[\SensitiveParameter] string $password, string $role): User
    {
        $values = ['name' => $name, 'email' => strtolower($email), 'password' => $password, 'role' => $role];
        Validator::make($values, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:254', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'max:128', Password::min(12)->mixedCase()->numbers()->symbols()->uncompromised()],
            'role' => ['required', Rule::in(AdminAccess::ROLES)],
        ])->validate();

        return DB::transaction(function () use ($values, $role): User {
            $user = User::query()->create(['name' => $values['name'], 'email' => $values['email'], 'password' => $values['password']]);
            $user->assignRole($role);
            activity('administration')->causedBy($user)->event('admin_provisioned')->withProperties(['role' => $role])->log('admin_provisioned');

            return $user;
        });
    }
}
