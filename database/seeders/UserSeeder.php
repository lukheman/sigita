<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin (Kepala Puskesmas / IT)
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password123'),
                'role' => Role::ADMIN,
            ]
        );

        // Petugas (Bidan Desa / Petugas Gizi)
        $petugasList = [
            ['name' => 'Bidan Siti Aminah', 'email' => 'siti@sigita.test'],
            ['name' => 'Bidan Nur Hasanah', 'email' => 'nur@sigita.test'],
            ['name' => 'Petugas Gizi Ahmad', 'email' => 'ahmad@sigita.test'],
        ];

        foreach ($petugasList as $petugas) {
            User::updateOrCreate(
                ['email' => $petugas['email']],
                [
                    'name' => $petugas['name'],
                    'password' => Hash::make('password123'),
                    'role' => Role::PETUGAS,
                ]
            );
        }

        $this->command->info('✓ UserSeeder: 1 Admin + 3 Petugas berhasil dibuat');
    }
}
