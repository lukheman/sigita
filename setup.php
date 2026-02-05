#!/usr/bin/env php
<?php

/**
 * Script Setup Project Laravel - SIGITA
 * 
 * Script ini akan melakukan setup lengkap untuk project Laravel:
 * 1. Composer install
 * 2. Copy .env.example ke .env
 * 3. Generate application key
 * 4. Konfigurasi database MySQL
 * 5. Menjalankan migrasi database
 * 6. Menjalankan seeder database
 * 7. Menjalankan artisan serve
 * 
 * Penggunaan: php setup.php
 * 
 * @author Akmal
 * @instagram @lukheeman
 * @phone 082250212121
 * @portfolio https://lukheman.github.io/portfolio/
 */

// =============================================================================
// KONFIGURASI APLIKASI
// =============================================================================

define('APP_NAME', 'SIGITA');
define('APP_DESCRIPTION', 'Sistem Informasi Gizi Balita');
define('APP_ICON', '💚');
define('DB_NAME_DEFAULT', 'sigita');

// Deteksi sistem operasi
define('IS_WINDOWS', PHP_OS_FAMILY === 'Windows');

// Enable ANSI colors di Windows 10+
if (IS_WINDOWS) {
    if (function_exists('sapi_windows_vt100_support')) {
        @sapi_windows_vt100_support(STDOUT, true);
        @sapi_windows_vt100_support(STDERR, true);
    }
}

// =============================================================================
// CLASS WARNA
// =============================================================================

class Warna
{
    private static bool $warnaAktif = true;

    public const RESET = "\033[0m";
    public const HIJAU = "\033[32m";
    public const MERAH = "\033[31m";
    public const KUNING = "\033[33m";
    public const BIRU = "\033[34m";
    public const CYAN = "\033[36m";
    public const TEBAL = "\033[1m";

    public static function nonaktifkanWarna(): void
    {
        self::$warnaAktif = false;
    }

    public static function apakahAktif(): bool
    {
        return self::$warnaAktif;
    }

    public static function get(string $warna): string
    {
        return self::$warnaAktif ? $warna : '';
    }
}

// =============================================================================
// HELPER FUNCTIONS
// =============================================================================

function cekDukunganWarna(): bool
{
    if (IS_WINDOWS) {
        $version = php_uname('v');
        if (preg_match('/build (\d+)/i', $version, $matches)) {
            return (int) $matches[1] >= 10586;
        }
        return false;
    }
    return true;
}

if (!cekDukunganWarna()) {
    Warna::nonaktifkanWarna();
}

function cetakPesan(string $pesan, string $warna = Warna::RESET): void
{
    if (Warna::apakahAktif()) {
        echo $warna . $pesan . Warna::RESET . PHP_EOL;
    } else {
        echo $pesan . PHP_EOL;
    }
}

function cetakLangkah(int $langkah, string $judul): void
{
    echo PHP_EOL;
    cetakPesan("===============================================================", Warna::CYAN);
    cetakPesan("  Langkah {$langkah}: {$judul}", Warna::TEBAL . Warna::CYAN);
    cetakPesan("===============================================================", Warna::CYAN);
    echo PHP_EOL;
}

function cetakSukses(string $pesan): void
{
    $simbol = IS_WINDOWS && !Warna::apakahAktif() ? "[OK]" : "✓";
    cetakPesan("  {$simbol} " . $pesan, Warna::HIJAU);
}

function cetakError(string $pesan): void
{
    $simbol = IS_WINDOWS && !Warna::apakahAktif() ? "[ERROR]" : "✗";
    cetakPesan("  {$simbol} " . $pesan, Warna::MERAH);
}

function cetakPeringatan(string $pesan): void
{
    $simbol = IS_WINDOWS && !Warna::apakahAktif() ? "[WARN]" : "⚠";
    cetakPesan("  {$simbol} " . $pesan, Warna::KUNING);
}

function cetakInfo(string $pesan): void
{
    $simbol = IS_WINDOWS && !Warna::apakahAktif() ? "->" : "→";
    cetakPesan("  {$simbol} " . $pesan, Warna::BIRU);
}

function jalankanCommand(string $command, bool $tampilkanOutput = true): bool
{
    cetakInfo("Menjalankan: {$command}");
    echo PHP_EOL;

    $descriptorSpec = [
        0 => STDIN,
        1 => STDOUT,
        2 => STDERR,
    ];

    $proses = proc_open($command, $descriptorSpec, $pipes);

    if (is_resource($proses)) {
        $kodeReturn = proc_close($proses);
        echo PHP_EOL;
        return $kodeReturn === 0;
    }

    return false;
}

function commandTersedia(string $command): bool
{
    if (IS_WINDOWS) {
        $result = shell_exec("where {$command} 2>NUL");
    } else {
        $result = shell_exec("which {$command} 2>/dev/null");
    }
    return !empty(trim($result ?? ''));
}

function dapatkanVersi(string $command): string
{
    if (IS_WINDOWS) {
        $output = shell_exec("{$command} --version 2>NUL");
    } else {
        $output = shell_exec("{$command} --version 2>/dev/null");
    }

    if (empty($output)) {
        return 'Tidak diketahui';
    }

    $lines = explode("\n", trim($output));
    return $lines[0] ?? 'Tidak diketahui';
}

function bacaInput(string $prompt, string $default = ''): string
{
    $defaultText = $default !== '' ? " [{$default}]" : '';
    echo Warna::get(Warna::KUNING) . "  {$prompt}{$defaultText}: " . Warna::get(Warna::RESET);

    $handle = fopen("php://stdin", "r");
    $input = trim(fgets($handle));

    return $input !== '' ? $input : $default;
}

function updateEnv(string $pathEnv, string $key, string $value): bool
{
    $content = file_get_contents($pathEnv);
    $escapedValue = $value;

    if (preg_match("/^{$key}=.*/m", $content)) {
        $content = preg_replace("/^{$key}=.*/m", "{$key}={$escapedValue}", $content);
    } else {
        $content .= PHP_EOL . "{$key}={$escapedValue}";
    }

    return file_put_contents($pathEnv, $content) !== false;
}

function tampilkanKontak(): void
{
    $telepon = IS_WINDOWS && !Warna::apakahAktif() ? "[KONTAK]" : "📞";
    $orang = IS_WINDOWS && !Warna::apakahAktif() ? "*" : "👤";
    $hp = IS_WINDOWS && !Warna::apakahAktif() ? "*" : "📱";
    $tel = IS_WINDOWS && !Warna::apakahAktif() ? "*" : "📞";
    $web = IS_WINDOWS && !Warna::apakahAktif() ? "*" : "🌐";

    cetakPesan(PHP_EOL);
    cetakPesan("+---------------------------------------------------------------+", Warna::TEBAL . Warna::CYAN);
    cetakPesan("|                     {$telepon} INFORMASI KONTAK {$telepon}                    |", Warna::TEBAL . Warna::CYAN);
    cetakPesan("+---------------------------------------------------------------+", Warna::TEBAL . Warna::CYAN);
    cetakPesan("|                                                               |", Warna::TEBAL . Warna::CYAN);
    cetakPesan("|   Dikembangkan oleh:                                          |", Warna::TEBAL . Warna::CYAN);
    cetakPesan("|                                                               |", Warna::TEBAL . Warna::CYAN);
    cetakPesan("|   {$orang} Nama      : Akmal                                        |", Warna::TEBAL . Warna::CYAN);
    cetakPesan("|   {$hp} Instagram : @lukheeman                                   |", Warna::TEBAL . Warna::CYAN);
    cetakPesan("|   {$tel} No. HP    : 082250212121                                 |", Warna::TEBAL . Warna::CYAN);
    cetakPesan("|   {$web} Portfolio : https://lukheman.github.io/portfolio/        |", Warna::TEBAL . Warna::CYAN);
    cetakPesan("|   {$web} Website   : https://aplikasita.my.id/                    |", Warna::TEBAL . Warna::CYAN);
    cetakPesan("|                                                               |", Warna::TEBAL . Warna::CYAN);
    cetakPesan("|   Silahkan hubungi untuk pertanyaan atau bantuan!             |", Warna::TEBAL . Warna::CYAN);
    cetakPesan("|                                                               |", Warna::TEBAL . Warna::CYAN);
    cetakPesan("+---------------------------------------------------------------+", Warna::TEBAL . Warna::CYAN);
    cetakPesan(PHP_EOL);
}

// =============================================================================
// SCRIPT UTAMA
// =============================================================================

$pathProject = __DIR__;
chdir($pathProject);

$sukses = IS_WINDOWS && !Warna::apakahAktif() ? "[SUCCESS]" : "✅";

cetakPesan(PHP_EOL);
cetakPesan("+---------------------------------------------------------------+", Warna::TEBAL . Warna::HIJAU);
cetakPesan("|                                                               |", Warna::TEBAL . Warna::HIJAU);
cetakPesan("|     " . APP_ICON . " " . APP_NAME . " - SCRIPT SETUP PROJECT " . APP_ICON . "                   |", Warna::TEBAL . Warna::HIJAU);
cetakPesan("|     " . APP_DESCRIPTION . "                              |", Warna::TEBAL . Warna::HIJAU);
cetakPesan("|                                                               |", Warna::TEBAL . Warna::HIJAU);
cetakPesan("+---------------------------------------------------------------+", Warna::TEBAL . Warna::HIJAU);
cetakPesan(PHP_EOL);

cetakInfo("Lokasi Project: {$pathProject}");
cetakInfo("Versi PHP: " . phpversion());
cetakInfo("Sistem Operasi: " . PHP_OS_FAMILY . " (" . php_uname('s') . " " . php_uname('r') . ")");

// =============================================================================
// LANGKAH 0: CEK KEBUTUHAN SISTEM
// =============================================================================

cetakLangkah(0, "Mengecek Kebutuhan Sistem");

$kebutuhan = [
    'php' => 'PHP CLI',
    'composer' => 'Composer',
];

$kebutuhanKurang = [];

foreach ($kebutuhan as $command => $nama) {
    if (commandTersedia($command)) {
        $versi = dapatkanVersi($command);
        cetakSukses("{$nama} ditemukan: {$versi}");
    } else {
        cetakError("{$nama} tidak ditemukan!");
        $kebutuhanKurang[] = $nama;
    }
}

if (commandTersedia('mysql')) {
    $versiMysql = dapatkanVersi('mysql');
    cetakSukses("MySQL Client ditemukan: {$versiMysql}");
} else {
    cetakPeringatan("MySQL Client tidak ditemukan (pastikan MySQL Server sudah berjalan)");
}

if (!empty($kebutuhanKurang)) {
    cetakPesan(PHP_EOL);
    cetakError("Kebutuhan yang belum terpenuhi: " . implode(', ', $kebutuhanKurang));
    cetakError("Silahkan install terlebih dahulu dan coba lagi.");
    exit(1);
}

// =============================================================================
// LANGKAH 1: COMPOSER INSTALL
// =============================================================================

cetakLangkah(1, "Menginstall Dependensi Composer");

if (file_exists("{$pathProject}/vendor")) {
    cetakPeringatan("Folder vendor/ sudah ada.");
    cetakInfo("Menjalankan composer install untuk memastikan dependensi terbaru...");
}

if (!jalankanCommand("composer install --no-interaction")) {
    cetakError("Composer install gagal!");
    exit(1);
}

cetakSukses("Dependensi Composer berhasil diinstall!");

// =============================================================================
// LANGKAH 2: COPY FILE .ENV
// =============================================================================

cetakLangkah(2, "Menyiapkan File Environment");

$pathEnv = "{$pathProject}/.env";
$pathEnvExample = "{$pathProject}/.env.example";

if (file_exists($pathEnv)) {
    cetakPeringatan("File .env sudah ada, melewati proses copy.");
    cetakInfo("Konfigurasi database akan diupdate di langkah berikutnya.");
} else {
    if (!file_exists($pathEnvExample)) {
        cetakError("File .env.example tidak ditemukan!");
        exit(1);
    }

    if (copy($pathEnvExample, $pathEnv)) {
        cetakSukses("File .env berhasil dibuat dari .env.example");
    } else {
        cetakError("Gagal menyalin .env.example ke .env");
        exit(1);
    }
}

// =============================================================================
// LANGKAH 3: GENERATE APPLICATION KEY
// =============================================================================

cetakLangkah(3, "Membuat Application Key");

$kontenEnv = file_get_contents($pathEnv);
if (preg_match('/^APP_KEY=base64:.+/m', $kontenEnv)) {
    cetakPeringatan("Application key sudah ada, melewati proses generate.");
} else {
    if (!jalankanCommand("php artisan key:generate --ansi")) {
        cetakError("Gagal membuat application key!");
        exit(1);
    }
    cetakSukses("Application key berhasil dibuat!");
}

// =============================================================================
// LANGKAH 4: KONFIGURASI DATABASE MYSQL
// =============================================================================

cetakLangkah(4, "Konfigurasi Database MySQL");

cetakInfo("Silahkan masukkan konfigurasi database MySQL:");
cetakPesan("");

$dbHost = bacaInput("Host MySQL", "127.0.0.1");
$dbPort = bacaInput("Port MySQL", "3306");
$dbName = bacaInput("Nama Database", DB_NAME_DEFAULT);
$dbUser = bacaInput("Username MySQL", "root");
$dbPass = bacaInput("Password MySQL", "");

cetakPesan("");
cetakInfo("Menyimpan konfigurasi database ke .env...");

updateEnv($pathEnv, 'DB_CONNECTION', 'mysql');
updateEnv($pathEnv, 'DB_HOST', $dbHost);
updateEnv($pathEnv, 'DB_PORT', $dbPort);
updateEnv($pathEnv, 'DB_DATABASE', $dbName);
updateEnv($pathEnv, 'DB_USERNAME', $dbUser);
updateEnv($pathEnv, 'DB_PASSWORD', $dbPass);

cetakSukses("Konfigurasi database berhasil disimpan!");

cetakPesan("");
cetakInfo("Ringkasan konfigurasi database:");
cetakPesan("     Host     : {$dbHost}", Warna::CYAN);
cetakPesan("     Port     : {$dbPort}", Warna::CYAN);
cetakPesan("     Database : {$dbName}", Warna::CYAN);
cetakPesan("     Username : {$dbUser}", Warna::CYAN);
cetakPesan("     Password : " . ($dbPass ? str_repeat('*', strlen($dbPass)) : '(kosong)'), Warna::CYAN);
cetakPesan("");

cetakPeringatan("PENTING: Pastikan database '{$dbName}' sudah dibuat di MySQL!");
cetakInfo("Jika belum, buat dengan perintah: CREATE DATABASE {$dbName};");
cetakPesan("");

// =============================================================================
// LANGKAH 5: MENJALANKAN MIGRASI
// =============================================================================

cetakLangkah(5, "Menjalankan Migrasi Database");

if (!jalankanCommand("php artisan migrate --force")) {
    cetakError("Migrasi database gagal!");
    cetakPesan("");
    cetakPeringatan("Kemungkinan penyebab:");
    cetakPesan("  1. Database '{$dbName}' belum dibuat", Warna::KUNING);
    cetakPesan("  2. MySQL Server tidak berjalan", Warna::KUNING);
    cetakPesan("  3. Kredensial database salah", Warna::KUNING);
    cetakPesan("");
    cetakInfo("Silahkan perbaiki dan jalankan ulang script ini.");
    exit(1);
}

cetakSukses("Migrasi database berhasil dijalankan!");

// =============================================================================
// LANGKAH 6: MENJALANKAN SEEDER
// =============================================================================

cetakLangkah(6, "Menjalankan Seeder Database");

if (!jalankanCommand("php artisan db:seed --force")) {
    cetakError("Seeder database gagal!");
    exit(1);
}

cetakSukses("Seeder database berhasil dijalankan!");

// =============================================================================
// LANGKAH 7: PENGECEKAN AKHIR
// =============================================================================

cetakLangkah(7, "Pengecekan Akhir");

if (!file_exists("{$pathProject}/public/storage")) {
    cetakInfo("Membuat symlink storage...");
    if (!jalankanCommand("php artisan storage:link")) {
        cetakPeringatan("Gagal membuat storage link (tidak kritis)");
    } else {
        cetakSukses("Symlink storage berhasil dibuat!");
    }
} else {
    cetakSukses("Symlink storage sudah ada.");
}

cetakInfo("Mengoptimasi aplikasi...");
jalankanCommand("php artisan config:clear");
jalankanCommand("php artisan view:clear");
jalankanCommand("php artisan route:clear");

// =============================================================================
// SELESAI!
// =============================================================================

cetakPesan(PHP_EOL);
cetakPesan("+---------------------------------------------------------------+", Warna::TEBAL . Warna::HIJAU);
cetakPesan("|                                                               |", Warna::TEBAL . Warna::HIJAU);
cetakPesan("|         {$sukses} SETUP PROJECT BERHASIL DISELESAIKAN! {$sukses}            |", Warna::TEBAL . Warna::HIJAU);
cetakPesan("|                                                               |", Warna::TEBAL . Warna::HIJAU);
cetakPesan("+---------------------------------------------------------------+", Warna::TEBAL . Warna::HIJAU);
cetakPesan(PHP_EOL);

cetakInfo("Aplikasi " . APP_NAME . " siap dijalankan!");
cetakPesan(PHP_EOL);
cetakPesan("  Untuk menjalankan server development, gunakan perintah:", Warna::KUNING);
cetakPesan("  " . Warna::get(Warna::TEBAL) . "php serve.php" . Warna::get(Warna::RESET) . " atau " . Warna::get(Warna::TEBAL) . "php artisan serve" . Warna::get(Warna::RESET));
cetakPesan(PHP_EOL);

tampilkanKontak();

cetakPesan("===============================================================", Warna::CYAN);
cetakPesan("  Apakah Anda ingin menjalankan server development sekarang? (y/n)", Warna::TEBAL . Warna::CYAN);
cetakPesan("===============================================================", Warna::CYAN);

$handle = fopen("php://stdin", "r");
$baris = fgets($handle);

if (trim(strtolower($baris)) === 'y' || trim(strtolower($baris)) === 'ya') {
    cetakPesan(PHP_EOL);
    cetakInfo("Menjalankan server development di http://localhost:8000");
    cetakInfo("Tekan Ctrl+C untuk menghentikan server.");
    cetakPesan(PHP_EOL);

    passthru("php artisan serve");
} else {
    cetakSukses("Setup selesai! Jalankan 'php serve.php' jika Anda sudah siap.");
}

fclose($handle);
