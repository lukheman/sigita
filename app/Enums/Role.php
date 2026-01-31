<?php

namespace App\Enums;

/**
 * Enum untuk role pengguna sistem.
 * 
 * - Admin: Kepala Puskesmas / IT
 * - Petugas: Bidan Desa / Petugas Gizi
 */
enum Role: string
{
    case ADMIN = 'admin';
    case PETUGAS = 'petugas';

    /**
     * Mendapatkan label yang ramah pengguna untuk ditampilkan di UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::PETUGAS => 'Petugas',
        };
    }

    /**
     * Mendapatkan deskripsi lengkap dari role.
     */
    public function description(): string
    {
        return match ($this) {
            self::ADMIN => 'Kepala Puskesmas / IT',
            self::PETUGAS => 'Bidan Desa / Petugas Gizi',
        };
    }

    /**
     * Mendapatkan warna badge untuk UI.
     */
    public function color(): string
    {
        return match ($this) {
            self::ADMIN => 'red',
            self::PETUGAS => 'blue',
        };
    }

    /**
     * Mendapatkan semua role sebagai array untuk dropdown/select.
     * 
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn(Role $role) => [
            $role->value => $role->label(),
        ])->toArray();
    }

    /**
     * Mengecek apakah role adalah admin.
     */
    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    /**
     * Mengecek apakah role adalah petugas.
     */
    public function isPetugas(): bool
    {
        return $this === self::PETUGAS;
    }
}
