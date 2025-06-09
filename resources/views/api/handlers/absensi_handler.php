<?php

function handle_absensi($koneksi, $method, $path) {
    if ($method == 'POST') {
        // Panggil fungsi untuk membuat absensi baru
        create_absensi($koneksi);
    } else {
        http_response_code(405);
        echo json_encode(['message' => 'Method Not Allowed. Use POST to create attendance.']);
    }
}

function create_absensi($koneksi) {
    // Ambil data JSON yang dikirim di body request
    $data = json_decode(file_get_contents("php://input"));

    // Validasi data input
    if (!isset($data->id_karyawan) || !isset($data->status)) {
        http_response_code(400); // Bad Request
        echo json_encode(['message' => 'Missing required fields: id_karyawan and status']);
        return;
    }

    $id_karyawan = intval($data->id_karyawan);
    $status = mysqli_real_escape_string($koneksi, $data->status); // Security: cegah SQL Injection
    $waktu = date('Y-m-d H:i:s'); // Waktu saat ini

    // Asumsi nama tabel adalah tb_absensi
    $query = "INSERT INTO tb_absensi (id_karyawan, waktu_absen, status) VALUES ('$id_karyawan', '$waktu', '$status')";

    if (mysqli_query($koneksi, $query)) {
        http_response_code(201); // Created
        echo json_encode(['message' => 'Attendance recorded successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Failed to record attendance', 'error' => mysqli_error($koneksi)]);
    }
}
?>