<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Employment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EmployementApiController extends Controller
{
    /**
     * Menampilkan daftar semua karyawan.
     */
    public function index(Request $request)
    {
        // Mengambil workshop_uuid dari user (owner) yang sedang terotentikasi
        //$workshopUuid = $request->user()->workshop_uuid;

        // Filter karyawan berdasarkan workshop_uuid milik owner
        //$employee = Employment::where('workshop_uuid', $workshopUuid)->get();

        $employee = Employment::all();

        return response()->json(['message' => 'Success', 'data' => $employee], 200);
    }

    /**
     * Menyimpan karyawan baru.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'workshop_uuid' => 'required|uuid|exists:workshops,id',
            // 'code' Dihapus dari sini, akan di-generate otomatis
            'name' => 'required|string|max:255',
            'role' => 'required|string|in:admin,mechanic',
            'description' => 'nullable|string',
            'email' => 'required|string|email|max:255|unique:employments,email',
            'password' => 'required|string|min:8',
            'photo' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        // Mengunci tabel untuk mencegah race condition saat membuat kode baru
        DB::beginTransaction();
        try {
            // Cari kode 'ST' terakhir, urutkan, dan kunci barisnya
            $lastEmployee = Employment::where('code', 'LIKE', 'ST%')
                ->orderBy('code', 'desc')
                ->lockForUpdate() // Kunci untuk transaksi
                ->first();

            $nextNumber = 1;
            if ($lastEmployee) {
                // Ambil angka dari kode terakhir (misal: 'ST00001' -> 1)
                $lastNumber = (int) substr($lastEmployee->code, 2);
                $nextNumber = $lastNumber + 1;
            }

            // Format kode baru: ST + 5 digit angka (misal: ST00001)
            $validatedData['code'] = 'ST' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

            // --- AKHIR LOGIKA AUTO-GENERATE CODE ---

            // Hash password sebelum disimpan
            $validatedData['password'] = Hash::make($validatedData['password']);

            // Asumsi Model Employment Anda menggunakan trait HasUuids untuk 'id'
            $employee = Employment::create($validatedData);

            DB::commit(); // Sukses, simpan perubahan

            return response()->json(['message' => 'Employee created successfully', 'data' => $employee], 201);

        } catch (Exception $e) {
            DB::rollBack(); // Gagal, batalkan perubahan
            return response()->json(['message' => 'Failed to create employee, please try again.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Menampilkan detail satu karyawan.
     *
     * @param  Employment  $employment
     */
    public function show(Request $request, string $employment)
    {
        // 2. Cari data secara manual menggunakan ID
        $employee = Employment::find($employment);

        // 3. Jika tidak ditemukan, kirim respon JSON 404
        if (!$employee) {
            return response()->json(['message' => 'Data karyawan tidak ditemukan.'], 404);
        }

        // (Ini asumsi auth:sanctum NONAKTIF)
        // Jika auth AKTIF, uncomment baris pengecekan
        /*
        // Otorisasi: Pastikan owner hanya bisa lihat employee di workshopnya
        if ($employee->workshop_uuid !== $request->user()->workshop_uuid) {
            return response()->json(['message' => 'Unauthorized. You do not own this resource.'], 403);
        }
        */

        // 4. Jika lolos (atau auth nonaktif), tampilkan data
        return response()->json(['message' => 'Success', 'data' => $employee], 200);
    }

    /**
     * Memperbarui data karyawan.
     *
     * @param  Employment  $employment
     */
    public function update(Request $request, Employment $employment)
    {
        // Pastikan owner hanya bisa update employee di workshopnya
//        if ($employment->workshop_uuid !== $request->user()->workshop_uuid) {
//            return response()->json(['message' => 'Forbidden'], 403);
//        }

        $validator = Validator::make($request->all(), [
            // workshop_uuid dan code sebaiknya tidak bisa diubah
            'name' => 'sometimes|required|string|max:255',
            'role' => 'sometimes|required|string|in:admin,mechanic',
            'description' => 'nullable|string',
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('employments', 'email')->ignore($employment->id),
            ],
            'password' => 'nullable|string|min:8', // Hanya update jika diisi
            'photo' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        if ($request->filled('password')) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        $employment->update($validatedData);

        return response()->json(['message' => 'Employee updated successfully', 'data' => $employment], 200);
    }

    /**
     * Menghapus data karyawan.
     *
     * @param  Employment  $employment
     */
    public function destroy(Employment $employment)
    {
        // Pastikan owner hanya bisa delete employee di workshopnya
    //        if ($employment->workshop_uuid !== request()->user()->workshop_uuid) {
    //            return response()->json(['message' => 'Forbidden'], 403);
    //        }

        $employment->delete();
        return response()->json(['message' => 'Employee deleted successfully', 'data' => $employment], 200);
    }
}
