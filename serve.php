#!/usr/bin/env php
<?php

/**
 * Script untuk menjalankan Laravel Development Server
 * dan otomatis membuka browser ke http://localhost:8000
 * 
 * Penggunaan: php serve.php
 * 
 * @author Akmal
 * @instagram @lukheeman
 * @phone 082250212121
 * @portfolio https://lukheman.github.io/portfolio/
 */

// Konfigurasi Aplikasi
define('APP_NAME', 'SIGITA');
define('APP_DESCRIPTION', 'Sistem Informasi Gizi Balita');
define('APP_ICON', '💚');

// Deteksi sistem operasi
define('IS_WINDOWS', PHP_OS_FAMILY === 'Windows');
define('IS_MAC', PHP_OS_FAMILY === 'Darwin');
define('IS_LINUX', PHP_OS_FAMILY === 'Linux');

// Warna untuk output console
class Warna
{
    public const RESET = "\033[0m";
    public const HIJAU = "\033[32m";
    public const MERAH = "\033[31m";
    public const KUNING = "\033[33m";
    public const BIRU = "\033[34m";
    public const CYAN = "\033[36m";
    public const TEBAL = "\033[1m";
}

// Enable ANSI colors di Windows 10+
if (IS_WINDOWS && function_exists('sapi_windows_vt100_support')) {
    @sapi_windows_vt100_support(STDOUT, true);
}

/**
 * Cetak pesan dengan warna
 */
function cetakPesan(string $pesan, string $warna = Warna::RESET): void
{
    echo $warna . $pesan . Warna::RESET . PHP_EOL;
}

/**
 * Cetak pesan info
 */
function cetakInfo(string $pesan): void
{
    cetakPesan("  → " . $pesan, Warna::BIRU);
}

/**
 * Cetak pesan sukses
 */
function cetakSukses(string $pesan): void
{
    cetakPesan("  ✓ " . $pesan, Warna::HIJAU);
}

/**
 * Buka URL di browser default
 */
function bukaBrowser(string $url): void
{
    cetakInfo("Membuka browser ke {$url}...");

    if (IS_WINDOWS) {
        pclose(popen("start {$url}", "r"));
    } elseif (IS_MAC) {
        exec("open {$url} > /dev/null 2>&1 &");
    } else {
        $browsers = ['xdg-open', 'gnome-open', 'kde-open', 'sensible-browser', 'x-www-browser'];
        $opened = false;

        foreach ($browsers as $browser) {
            $check = shell_exec("which {$browser} 2>/dev/null");
            if (!empty(trim($check ?? ''))) {
                exec("{$browser} {$url} > /dev/null 2>&1 &");
                $opened = true;
                break;
            }
        }

        if (!$opened) {
            cetakPesan("  ⚠ Tidak dapat membuka browser secara otomatis.", Warna::KUNING);
            cetakPesan("    Silahkan buka manual: {$url}", Warna::KUNING);
        }
    }
}

// =============================================================================
// SCRIPT UTAMA
// =============================================================================

$pathProject = __DIR__;
chdir($pathProject);

$url = "http://localhost:8000";

cetakPesan(PHP_EOL);
cetakPesan("+---------------------------------------------------------------+", Warna::TEBAL . Warna::HIJAU);
cetakPesan("|                                                               |", Warna::TEBAL . Warna::HIJAU);
cetakPesan("|     " . APP_ICON . " " . APP_NAME . " - DEVELOPMENT SERVER " . APP_ICON . "                     |", Warna::TEBAL . Warna::HIJAU);
cetakPesan("|     " . APP_DESCRIPTION . "                              |", Warna::TEBAL . Warna::HIJAU);
cetakPesan("|                                                               |", Warna::TEBAL . Warna::HIJAU);
cetakPesan("+---------------------------------------------------------------+", Warna::TEBAL . Warna::HIJAU);
cetakPesan(PHP_EOL);

cetakInfo("Lokasi Project: {$pathProject}");
cetakInfo("Server URL: {$url}");
cetakPesan("");

cetakSukses("Menjalankan server development...");
cetakPesan("");

// Buka browser di background setelah delay
if (IS_WINDOWS) {
    pclose(popen("ping -n 3 127.0.0.1 > nul && start {$url}", "r"));
} else {
    exec("(sleep 2 && xdg-open {$url} 2>/dev/null || open {$url} 2>/dev/null) &");
}

cetakPesan("===============================================================", Warna::CYAN);
cetakPesan("  Server berjalan di: " . Warna::TEBAL . $url . Warna::RESET, Warna::CYAN);
cetakPesan("  Tekan Ctrl+C untuk menghentikan server", Warna::CYAN);
cetakPesan("===============================================================", Warna::CYAN);
cetakPesan("");

// Jalankan artisan serve
passthru("php artisan serve");
