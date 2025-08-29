<?php

namespace Database\Seeders;

use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // === Superadmin idempotente ===
        $user = User::firstOrCreate(
            ['username' => 'superadmin'], // clave única
            [
                'id' => (string) Str::uuid(),
                'firstname' => 'Super',
                'lastname' => 'Admin',
                'email' => 'superadmin@starter-kit.com',
                'email_verified_at' => now(),
                'password' => Hash::make('superadmin'),
            ]
        );

        // Asignar/asegurar super_admin de Shield sobre este usuario
        Artisan::call('shield:super-admin', ['--user' => $user->id]);

        // === Relleno de usuarios por rol (idempotente) ===
        $roles = DB::table('roles')->whereNot('name', 'super_admin')->get();

        foreach ($roles as $role) {
            for ($i = 0; $i < 10; $i++) {

                $email = $faker->unique()->safeEmail();
                $username = $faker->unique()->userName();

                $u = User::firstOrCreate(
                    ['email' => $email],   // evita duplicar por correo
                    [
                        'id' => (string) Str::uuid(),
                        'username' => $username,
                        'firstname' => $faker->firstName(),
                        'lastname' => $faker->lastName(),
                        'email_verified_at' => now(),
                        'password' => Hash::make('password'),
                    ]
                );

                // Vincular rol de forma idempotente
                DB::table('model_has_roles')->updateOrInsert(
                    ['role_id' => $role->id, 'model_type' => User::class, 'model_id' => $u->id],
                    []
                );
            }
        }
    }
}
