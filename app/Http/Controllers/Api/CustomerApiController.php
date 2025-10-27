<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Employment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CustomerApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = Customer::all();
        return response()->json(['message'=>'Success', 'data'=>$customers],201);
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
            'phone' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        // Mengunci tabel untuk mencegah race condition saat membuat kode baru
        DB::beginTransaction();
        try {
            // Cari kode 'ST' terakhir, urutkan, dan kunci barisnya
            $lastCustomer = Customer::where('code', 'LIKE', 'CS%')
                ->orderBy('code', 'desc')
                ->lockForUpdate() // Kunci untuk transaksi
                ->first();

            $nextNumber = 1;
            if ($lastCustomer) {
                // Ambil angka dari kode terakhir (misal: 'ST00001' -> 1)
                $lastNumber = (int) substr($lastCustomer->code, 2);
                $nextNumber = $lastNumber + 1;
            }

            // Format kode baru: CS + 5 digit angka (misal: ST00001)
            $validatedData['code'] = 'CS' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

            // --- AKHIR LOGIKA AUTO-GENERATE CODE ---

            // Asumsi Model Employment Anda menggunakan trait HasUuids untuk 'id'
            $customer = Customer::create($validatedData);

            DB::commit(); // Sukses, simpan perubahan

            return response()->json(['message' => 'Customer created successfully', 'data' => $customer], 201);

        } catch (Exception $e) {
            DB::rollBack(); // Gagal, batalkan perubahan
            return response()->json(['message' => 'Failed to create customer, please try again.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        //
    }
}
