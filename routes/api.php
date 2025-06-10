<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB; // Pastikan ini ada
use Illuminate\Support\Facades\Validator;
use App\Models\Attendance;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rute default bawaan laravel
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * Rute untuk mendapatkan SEMUA data karyawan, 
 * lengkap dengan nama divisi dan jabatannya.
 */
Route::get('/karyawan', function () {
    try {
        $karyawan = DB::table('users')
            ->join('divisions', 'users.division_id', '=', 'divisions.id')
            ->join('job_titles', 'users.job_title_id', '=', 'job_titles.id')
            ->select(
                'users.id as id_karyawan',
                'users.name as nama_karyawan',
                'users.email',
                'divisions.name as divisi',
                'job_titles.name as jabatan'
            )
            ->get();
        
        return response()->json($karyawan);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Gagal mengambil data. Periksa nama kolom foreign key (misal: division_id).',
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::get('/karyawan/{id}', function ($id) {
    try {
        $karyawan = DB::table('users')
            ->join('divisions', 'users.division_id', '=', 'divisions.id')
            ->join('job_titles', 'users.job_title_id', '=', 'job_titles.id')
            ->select(
                'users.id as id_karyawan',
                'users.name as nama_karyawan',
                'users.email',
                'divisions.name as divisi',
                'job_titles.name as jabatan'
            )
            ->where('users.id', $id) // Mencari berdasarkan ID dari tabel users
            ->first();

        if ($karyawan) {
            return response()->json($karyawan);
        } else {
            return response()->json(['message' => 'Karyawan tidak ditemukan'], 404);
        }

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Gagal mengambil data. Periksa nama kolom foreign key (misal: division_id).',
            'error' => $e->getMessage()
        ], 500);
    }

    
});

/**
 * Rute untuk mendapatkan SEMUA data barcode lokasi.
 */
Route::get('/barcodes', function () {
    try {
        // Mengambil data dari tabel 'barcodes'
        $barcodes = DB::table('barcodes')
            ->select('id', 'name', 'value', 'latitude', 'longitude', 'radius') // Memilih kolom yang relevan
            ->get();
        
        return response()->json($barcodes);

    } catch (\Exception $e) {
        // Jika terjadi error saat mengambil data dari database
        return response()->json([
            'message' => 'Gagal mengambil data barcode',
            'error' => $e->getMessage()
        ], 500);
    }
});

/**
 * Rute untuk MENCATAT ABSENSI BARU (POST Request).
 * Rute ini akan melakukan validasi lokasi (geofencing).
 */
Route::post('/absensi', function (Request $request) {

    // 1. Validasi Input (tetap sama)
    $validator = Validator::make($request->all(), [
        'user_id'   => 'required|string|exists:users,id',
        'latitude'  => 'required|numeric',
        'longitude' => 'required|numeric',
        'status'    => 'required|in:masuk,pulang',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }
    $validatedData = $validator->validated();
    $userLat = $validatedData['latitude'];
    $userLon = $validatedData['longitude'];

    // 2. Logika Geofencing (dengan penambahan untuk menyimpan ID barcode)
    $lokasiKantor = DB::table('barcodes')->get();
    $beradaDiLokasi = false;
    $valid_barcode_id = null; // Variabel untuk menyimpan ID lokasi yang valid

    // Fungsi hitung jarak tetap sama
    function hitungJarak($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371000; $latFrom = deg2rad($lat1); $lonFrom = deg2rad($lon1); $latTo = deg2rad($lat2); $lonTo = deg2rad($lon2);
        $latDelta = $latTo - $latFrom; $lonDelta = $lonTo - $lonFrom;
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }

    foreach ($lokasiKantor as $lokasi) {
        if (hitungJarak($userLat, $userLon, $lokasi->latitude, $lokasi->longitude) <= $lokasi->radius) {
            $beradaDiLokasi = true;
            $valid_barcode_id = $lokasi->id; // Simpan ID barcode saat lokasi cocok
            break;
        }
    }

    if (!$beradaDiLokasi) {
        return response()->json(['message' => 'Anda berada di luar radius lokasi yang diizinkan.'], 422);
    }
    

    // =======================================================
    $today = now()->toDateString();
    
    // --- PROSES ABSEN MASUK ---
    if ($validatedData['status'] == 'masuk') {
        // Cek apakah hari ini sudah ada absen masuk untuk user ini
        $absenMasukHariIni = Attendance::where('user_id', $validatedData['user_id'])
                                        ->where('date', $today)
                                        ->first();

        if ($absenMasukHariIni) {
            return response()->json(['message' => 'Anda sudah melakukan absensi masuk hari ini.'], 409); // 409 Conflict
        }

        // Buat record absensi baru
        try {
            Attendance::create([
                'user_id'       => $validatedData['user_id'],
                'barcode_id'    => $valid_barcode_id,
                'date'          => $today,
                'time_in'       => now()->toTimeString(),
                'latitude'      => $userLat,
                'longitude'     => $userLon,
                'status'        => 'Hadir', // Atau status default lain sesuai sistem Anda
            ]);
            return response()->json(['message' => 'Absensi masuk berhasil dicatat.'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menyimpan data absensi.', 'error' => $e->getMessage()], 500);
        }
    }

    // --- PROSES ABSEN PULANG ---
    if ($validatedData['status'] == 'pulang') {
        // Cari data absen masuk hari ini
        $absenMasukHariIni = Attendance::where('user_id', $validatedData['user_id'])
                                        ->where('date', $today)
                                        ->first();

        if (!$absenMasukHariIni) {
            return response()->json(['message' => 'Anda belum melakukan absensi masuk hari ini.'], 422);
        }

        if (!is_null($absenMasukHariIni->time_out)) {
            return response()->json(['message' => 'Anda sudah melakukan absensi pulang hari ini.'], 409);
        }

        // Update record absensi dengan jam pulang
        try {
            $absenMasukHariIni->update([
                'time_out' => now()->toTimeString()
            ]);
            return response()->json(['message' => 'Absensi pulang berhasil dicatat.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui data absensi.', 'error' => $e->getMessage()], 500);
        }
    }
});

Route::get('/absensi/{userId}', function ($userId) {
    try {
        // Cek dulu apakah user ada
        if (!\App\Models\User::find($userId)) {
            return response()->json(['message' => 'User tidak ditemukan.'], 404);
        }

        $riwayatAbsensi = Attendance::where('user_id', $userId)
            ->with(['user:id,name', 'barcode:id,name', 'shift']) // Eager load relasi
            ->orderBy('date', 'desc') // Urutkan dari yang terbaru
            ->get();

        return response()->json($riwayatAbsensi);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Gagal mengambil riwayat absensi.',
            'error' => $e->getMessage()
        ], 500);
    }
});