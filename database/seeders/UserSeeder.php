<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name'     => 'Admin Principal',
            'email'    => 'admin@farmersmarket.ci',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        $supervisor = User::create([
            'name'          => 'Supervisor Abidjan',
            'email'         => 'supervisor@farmersmarket.ci',
            'password'      => Hash::make('password'),
            'role'          => 'supervisor',
            'supervisor_id' => $admin->id,
        ]);

        User::create([
            'name'          => 'Operateur POS 1',
            'email'         => 'operator1@farmersmarket.ci',
            'password'      => Hash::make('password'),
            'role'          => 'operator',
            'supervisor_id' => $supervisor->id,
        ]);

        User::create([
            'name'          => 'Operateur POS 2',
            'email'         => 'operator2@farmersmarket.ci',
            'password'      => Hash::make('password'),
            'role'          => 'operator',
            'supervisor_id' => $supervisor->id,
        ]);
    }
}