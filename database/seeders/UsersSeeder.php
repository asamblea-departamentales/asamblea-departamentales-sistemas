<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('SUPERADMIN_PASSWORD');

        if (! $password || strlen($password) < 12) {
            throw new \RuntimeException(
                'SUPERADMIN_PASSWORD must be defined in .env with at least 12 characters.'
            );
        }

        // 2. Superadmin (Idempotente)
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@asamblea.gob.sv'],
            [
                'id' => (string) Str::uuid(),
                'username' => 'superadmin',
                'firstname' => 'Admin',
                'lastname' => 'Principal',
                'email_verified_at' => now(),
                'password' => Hash::make($password),
            ]
        );

        // Vincular con Shield Super Admin
        Artisan::call('shield:super-admin', ['--user' => $superAdmin->id]);

        // Los usuarios reales ya no se crean aquí - se autenticarán contra LDAP
        // Los usuarios deben existir previamente en la tabla users con sus roles y departamental
        // La autenticación de contraseña se hace contra el directorio LDAP institucional
        //
        // foreach ($usuariosReales as $data) {
        //     $user = User::firstOrCreate(
        //         ['email' => $data['email']],
        //         [
        //             'id' => (string) Str::uuid(),
        //             'username' => $data['username'],
        //             'firstname' => $data['firstname'],
        //             'lastname' => $data['lastname'],
        //             'email_verified_at' => now(),
        //         ]
        //     );
        //     $user->syncRoles([$data['role']]);
        // }
    }
}
