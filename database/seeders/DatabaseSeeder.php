<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,      // 1. User (Admin & Petugas)
            DesaSeeder::class,      // 2. Desa (Wilayah)
            BalitaSeeder::class,    // 3. Balita (depends on Desa)
            PengukuranSeeder::class, // 4. Pengukuran (depends on Balita)
        ]);

        $this->command->info('');
        $this->command->info('✅ Semua seeder berhasil dijalankan!');
        $this->command->info('');
        $this->command->info('📝 Akun Login:');
        $this->command->info('   Admin   : admin@sigita.test / password');
        $this->command->info('   Petugas : siti@sigita.test / password');
    }
}
