<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Création des rôles Spatie
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $redacteur  = Role::firstOrCreate(['name' => 'redacteur']);
        $validateur = Role::firstOrCreate(['name' => 'validateur']);

        // 2. Création de ton utilisateur Admin
        $user = User::firstOrCreate(
            ['email' => 'admin@fama.ml'],
            [
                'name' => 'Admin FAMA',
                'password' => Hash::make('password'), // Change le mot de passe après !
            ]
        );

        // 3. Assignation du rôle
        $user->assignRole($superAdmin);
        
        $this->command->info('Rôles créés et utilisateur admin configuré !');
    }
}
