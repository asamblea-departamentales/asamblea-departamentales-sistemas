<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Configuración de Usuarios Reales
        $usuariosReales = [
            [
                'firstname' => 'Rene',
                'lastname'  => 'Herrera',
                'email'     => 'rene.herrera@asamblea.gob.sv',
                'username'  => 'rherrera',
                'password'  => 'ReneAdministrador2026',
                'role'      => 'gol', // Rol GOL (lectura + export)
            ],
            [
                'firstname' => 'Marvin',
                'lastname'  => 'Isho',
                'email'     => 'marvin.isho@asamblea.gob.sv',
                'username'  => 'misho',
                'password'  => 'SonsonatePrueba2026',
                'role'      => 'coordinador',
            ],
            [
                'firstname' => 'Ana',
                'lastname'  => 'Estrada',
                'email'     => 'ana.estrada@asamblea.gob.sv',
                'username'  => 'aestrada',
                'password'  => 'SantaAnaPrueba2026',
                'role'      => 'coordinador',
            ],
            [
                'firstname' => 'Monica',
                'lastname'  => 'Villeda',
                'email'     => 'monica.villeda@asamblea.gob.sv',
                'username'  => 'mvilleda',
                'password'  => 'LaLiberdadPrueba2026',
                'role'      => 'coordinador',
            ],
            [
                'firstname' => 'Amilcar',
                'lastname'  => 'Ortiz',
                'email'     => 'amilcar.ortiz@asamblea.gob.sv',
                'username'  => 'aortiz',
                'password'  => 'SanVicentePrueba2026',
                'role'      => 'coordinador',
            ],
            [
                'firstname' => 'Francisco',
                'lastname'  => 'Gonzalez',
                'email'     => 'francisco.gonzalez@asamblea.gob.sv',
                'username'  => 'fgonzalez',
                'password'  => 'LaUnionPrueba2026',
                'role'      => 'coordinador',
            ],
            [
                'firstname' => 'Departamental',
                'lastname'  => 'San Salvador',
                'email'     => 'pmunoz@asamblea.gob.sv',
                'username'  => 'pmunoz',
                'password'  => 'SanSalvador2026',
                'role'      => 'coordinador',
            ],
        ];

        // 2. Superadmin (Idempotente)
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@asamblea.gob.sv'],
            [
                'id' => (string) Str::uuid(),
                'username' => 'superadmin',
                'firstname' => 'Admin',
                'lastname' => 'Principal',
                'email_verified_at' => now(),
                'password' => Hash::make('Admin@2026!'),
            ]
        );

        // Vincular con Shield Super Admin
        Artisan::call('shield:super-admin', ['--user' => $superAdmin->id]);

        // 3. Crear usuarios y asignar roles mediante Spatie (usando nombres de tu RoleSeeder)
        foreach ($usuariosReales as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'id' => (string) Str::uuid(),
                    'username' => $data['username'],
                    'firstname' => $data['firstname'],
                    'lastname' => $data['lastname'],
                    'email_verified_at' => now(),
                    'password' => Hash::make($data['password']),
                ]
            );

            // Sincronizar el rol (esto evita duplicados y asegura que tenga el rol correcto)
            $user->syncRoles([$data['role']]);
        }
    }
}