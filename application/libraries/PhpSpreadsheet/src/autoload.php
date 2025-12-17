<?php
// Autoloader sederhana untuk PhpSpreadsheet
function myAutoloader($class)
{
    $prefix = 'PhpOffice\\PhpSpreadsheet\\';  // Namespace utama PhpSpreadsheet
    $base_dir = __DIR__ . '/libraries/PhpSpreadsheet/src/';  // Sesuaikan dengan lokasi folder PhpSpreadsheet

    // Jika kelasnya dimulai dengan namespace PhpOffice\PhpSpreadsheet
    if (strncmp($prefix, $class, strlen($prefix)) === 0) {
        // Ganti namespace dengan struktur direktori
        $relative_class = substr($class, strlen($prefix));
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        // Cek apakah file ada dan masukkan
        if (file_exists($file)) {
            require $file;
        }
    }
}

// Daftarkan autoloader
spl_autoload_register('myAutoloader');
