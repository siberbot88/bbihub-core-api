<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Voucher\StoreVoucherRequest;
use App\Http\Requests\Api\Voucher\UpdateVoucherRequest;
use App\Http\Resources\VoucherResource;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * @group Vouchers
 *
 * API untuk mengelola data voucher.
 */
class VoucherApiController extends Controller
{
    /**
     * Menampilkan daftar voucher.
     *
     * @queryParam status string Opsional. Filter berdasarkan status: 'active', 'expired', 'inactive', 'scheduled'. Example: active
     * @queryParam workshop_uuid string Opsional. Filter berdasarkan ID workshop. Example: 9a7b6cde-1234-5678-abcd-111122223333
     */
    public function index(Request $request)
    {
        $query = Voucher::query();

        // Filter berdasarkan workshop
        if ($request->has('workshop_uuid')) {
            $query->where('workshop_uuid', $request->workshop_uuid);
        }

        // Filter berdasarkan status
        if ($status = $request->query('status')) {
            $now = Carbon::now();
            match ($status) {
                'active' => $query->where('is_active', true)
                    ->where('valid_from', '<=', $now)
                    ->where('valid_until', '>=', $now),
                'expired' => $query->where('valid_until', '<', $now),
                'inactive' => $query->where('is_active', false),
                'scheduled' => $query->where('valid_from', '>', $now),
                default => null,
            };
        }

        $vouchers = $query->latest()->paginate(15);

        return VoucherResource::collection($vouchers);
    }

    /**
     * Menyimpan voucher baru.
     *
     * @bodyParam workshop_uuid string required UUID dari workshop. Example: 9a7b6cde-1234-5678-abcd-111122223333
     * @bodyParam code_voucher string required Kode unik voucher. Example: HEMAT50K
     * @bodyParam title string required Judul voucher. Example: Diskon Merdeka
     * @bodyParam discount_value float required Nilai diskon. Example: 50000
     * @bodyParam quota int required Jumlah total kuota. Example: 100
     * @bodyParam min_transaction float required Minimal transaksi. Example: 200000
     * @bodyParam valid_from date required Tanggal mulai berlaku. Example: 2025-12-01
     * @bodyParam valid_until date required Tanggal akhir berlaku. Example: 2025-12-31
     * @bodyParam is_active boolean Status aktif. Example: true
     * @bodyParam image file Opsional. Gambar voucher (jpg, png, webp).
     */
    public function store(StoreVoucherRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('vouchers', 'public');
        }

        $voucher = Voucher::create($data);

        return new VoucherResource($voucher);
    }

    /**
     * Menampilkan detail voucher.
     *
     * @urlParam voucher string required ID (UUID) dari voucher. Example: 9a7b6cde-4321-8765-abcd-333322221111
     * @responseFile status=404 scenario="Voucher tidak ditemukan" {"message": "Not Found."}
     */
    public function show(Voucher $voucher)
    {
        return new VoucherResource($voucher);
    }

    /**
     * Memperbarui voucher.
     *
     * @urlParam voucher string required ID (UUID) dari voucher. Example: 9a7b6cde-4321-8765-abcd-333322221111
     * @bodyParam image file Opsional. Gambar voucher baru.
     */
    public function update(UpdateVoucherRequest $request, Voucher $voucher)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($voucher->image) {
                Storage::disk('public')->delete($voucher->image);
            }
            $data['image'] = $request->file('image')->store('vouchers', 'public');
        }

        $voucher->update($data);

        return new VoucherResource($voucher);
    }

    /**
     * Menghapus voucher.
     *
     * @urlParam voucher string required ID (UUID) dari voucher. Example: 9a7b6cde-4321-8765-abcd-333322221111
     * @response 204 scenario="Voucher berhasil dihapus"
     */
    public function destroy(Voucher $voucher)
    {
        if ($voucher->image) {
            Storage::disk('public')->delete($voucher->image);
        }

        $voucher->delete();

        return response()->noContent();
    }
}
