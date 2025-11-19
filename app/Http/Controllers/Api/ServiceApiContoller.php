<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ServiceApiContoller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $service = Service::all();

        if (!$service) {
            return response()->json(['message' => 'Data karyawan tidak ditemukan.'], 404);
        }
        return response()->json(['message'=>'Success', 'data' => $service], 201);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|decimal',
            'scheduled_date' => 'required|date',
            'estimated_time' => 'required|date',
            'status' => 'required|in:pending,accept,in progress,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        // Mengunci tabel untuk mencegah race condition saat membuat kode baru
        DB::beginTransaction();
        try {
            // Cari kode 'SVC' terakhir, urutkan, dan kunci barisnya
            $lastServices = Service::where('code', 'LIKE', 'ST%')
                ->orderBy('code', 'desc')
                ->lockForUpdate() // Kunci untuk transaksi
                ->first();

            $nextNumber = 1;
            if ($lastServices) {
                // Ambil angka dari kode terakhir (misal: 'SVC00001' -> 1)
                $lastNumber = (int) substr($lastServices->code, 2);
                $nextNumber = $lastNumber + 1;
            }

            // Format kode baru: SVC + 5 digit angka (misal: SCV00001)
            $validatedData['code'] = 'SVC' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

            // --- AKHIR LOGIKA AUTO-GENERATE CODE ---

            // Hash password sebelum disimpan
            $validatedData['password'] = Hash::make($validatedData['password']);

            // Asumsi Model Service Anda menggunakan trait HasUuids untuk 'id'
            $services = Service::create($validatedData);

            DB::commit(); // Sukses, simpan perubahan

            return response()->json(['message' => 'Employee created successfully', 'data' => $services], 201);

        } catch (Exception $e) {
            DB::rollBack(); // Gagal, batalkan perubahan
            return response()->json(['message' => 'Failed to create employee, please try again.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        $service = Service::find($service);
        if (!$service) {
            return response()->json(['message' => 'Data karyawan tidak ditemukan.'], 404);
            }

        return response()->json(['message' => 'Success', 'data' => $service], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $service = Service::find($id);
        if (!$service) {
            return response()->json(['message' => 'Data karyawan tidak ditemukan.'], 404);
        }

        $service->delete();
    }
}
