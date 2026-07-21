<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AssignUserRoleSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('name', 'coordinador')->firstOrFail();

        $usernames = [
            'rene.herrera',
            'marvin.isho',
            'ana.estrada',
            'monica.villeda',
            'Amilcar.ortiz',
            'francisco.gonzalez',
            'p.munoz',
        ];

        foreach ($usernames as $username) {
            $user = User::where('username', $username)->first();
            if ($user) {
                $user->syncRoles([$role]);
                $this->command->info("Rol 'coordinador' asignado a {$username}");
            } else {
                $this->command->warn("Usuario {$username} no encontrado en la BD");
            }
        }
    }
}
