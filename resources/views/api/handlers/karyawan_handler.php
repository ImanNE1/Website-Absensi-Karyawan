<?php

function handle_karyawan($koneksi, $method, $path) {
    if ($method == 'GET') {
        // Cek apakah ada ID di URL (misal: /api/karyawan/123)
        if (isset($path[1]) && is_numeric($path[1])) {
            get_karyawan_by_id($koneksi, $path[1]);
        } else {
            get_all_karyawan($koneksi);
        }
    } else {
        // Metode selain GET tidak diizinkan untuk endpoint ini
        http_response_code(405); // Method Not Allowed
        echo json_encode(['message' => 'Method Not Allowed']);
    }
}

function get_all_karyawan($koneksi) {
    // Asumsi nama tabel adalah tb_karyawan
    $query = "SELECT id_karyawan, nama, jabatan FROM tb_karyawan"; 
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        $karyawan = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $karyawan[] = $row;
        }
        echo json_encode($karyawan);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Internal Server Error', 'error' => mysqli_error($koneksi)]);
    }
}

function get_karyawan_by_id($koneksi, $id) {
    $id = intval($id); // Security: pastikan ID adalah integer
    // Asumsi nama tabel adalah tb_karyawan
    $query = "SELECT id_karyawan, nama, jabatan, alamat FROM tb_karyawan WHERE id_karyawan = $id";
    $result = mysqli_query($koneksi, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $karyawan = mysqli_fetch_assoc($result);
        echo json_encode($karyawan);
    } else {
        http_response_code(404); // Not Found
        echo json_encode(['message' => 'Karyawan not found']);
    }
}
?>