<?php
// Set header untuk memberitahu browser bahwa outputnya adalah JSON
header("Content-Type: application/json; charset=UTF-8");

// Panggil file koneksi database Anda
require_once '../koneksi.php';

// Ambil request dari URL
$request_uri = isset($_GET['request']) ? $_GET['request'] : '';
$path = explode('/', $request_uri);

// Ambil metode HTTP (GET, POST, PUT, DELETE)
$method = $_SERVER['REQUEST_METHOD'];

// Routing sederhana berdasarkan bagian pertama dari URL
$endpoint = $path[0];

switch ($endpoint) {
    case 'karyawan':
        // Panggil file handler untuk karyawan
        require 'handlers/karyawan_handler.php';
        handle_karyawan($koneksi, $method, $path);
        break;

    case 'absensi':
        // Panggil file handler untuk absensi
        require 'handlers/absensi_handler.php';
        handle_absensi($koneksi, $method, $path);
        break;

    default:
        // Jika endpoint tidak ditemukan
        http_response_code(404);
        echo json_encode(['message' => 'Endpoint Not Found']);
        break;
}
?>