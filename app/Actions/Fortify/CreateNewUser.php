<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Pengguna;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        $email = strtolower(trim($input['email']));
        $name = trim($input['name']);

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $input['password'],
            'role' => 'pelanggan',
            'status' => 'aktif',
        ]);

        Pengguna::firstOrCreate(
            ['telepon_email' => $user->email],
            [
                'id_pengguna' => (string) Str::uuid(),
                'nama_lengkap' => $user->name,
                'peran' => 'pelanggan',
                'aktif' => true,
            ]
        );

        return $user;
    }
}
