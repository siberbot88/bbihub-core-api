<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Voucher\StoreVoucherRequest;
use App\Http\Requests\Api\Voucher\UpdateVoucherRequest;
use App\Http\Resources\VoucherResource;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class VoucherApiController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Voucher::class);

        $user = $request->user();

        $vouchers = Voucher::query()
            ->with('workshop')
            ->forUser($user)
            ->when(
                $request->filled('workshop_uuid'),
                fn ($q) => $q->where('workshop_uuid', $request->workshop_uuid)
            )
            ->status($request->query('status'))
            ->latest()
            ->paginate(15);

        return VoucherResource::collection($vouchers);
    }

    public function store(StoreVoucherRequest $request)
    {
        $data = $request->validated();

        // cek akses ke workshop
        Gate::authorize('create', [Voucher::class, $data['workshop_uuid']]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('vouchers', 'public');
        }

        $voucher = Voucher::create($data);

        return new VoucherResource($voucher);
    }

    public function show(Voucher $voucher)
    {
        $this->authorize('view', $voucher);

        return new VoucherResource($voucher);
    }

    public function update(UpdateVoucherRequest $request, Voucher $voucher)
    {
        $this->authorize('update', $voucher);

        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($voucher->image) {
                Storage::disk('public')->delete($voucher->image);
            }

            $data['image'] = $request->file('image')->store('vouchers', 'public');
        }

        $voucher->update($data);

        return new VoucherResource($voucher);
    }

    public function destroy(Voucher $voucher)
    {
        $this->authorize('delete', $voucher);

        if ($voucher->image) {
            Storage::disk('public')->delete($voucher->image);
        }

        $voucher->delete();

        return response()->noContent();
    }

    /**
     * POST /api/v1/vouchers/validate
     * Validate voucher code and calculate discount
     */
    public function validateVoucher(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'workshop_uuid' => 'nullable|string',
        ]);

        $voucher = Voucher::where('code_voucher', $data['code'])
            ->when($data['workshop_uuid'] ?? null, fn($q, $wid) => $q->where('workshop_uuid', $wid))
            ->first();

        if (!$voucher) {
            return response()->json([
                'valid' => false,
                'message' => 'Kode voucher tidak ditemukan.',
            ], 200);
        }

        // Check if voucher is active
        if ($voucher->status !== 'active') {
            return response()->json([
                'valid' => false,
                'message' => 'Voucher tidak aktif atau sudah kadaluarsa.',
            ], 200);
        }

        // Check quota
        if ($voucher->quota <= 0) {
            return response()->json([
                'valid' => false,
                'message' => 'Kuota voucher sudah habis.',
            ], 200);
        }

        // Check minimum transaction
        if ($data['amount'] < $voucher->min_transaction) {
            return response()->json([
                'valid' => false,
                'message' => 'Minimum transaksi Rp ' . number_format($voucher->min_transaction, 0, ',', '.'),
            ], 200);
        }

        // Calculate discount
        $discountAmount = min($voucher->discount_value, $data['amount']);
        $finalAmount = $data['amount'] - $discountAmount;

        return response()->json([
            'valid' => true,
            'voucher' => [
                'id' => $voucher->id,
                'code' => $voucher->code_voucher,
                'title' => $voucher->title,
                'discount_value' => $voucher->discount_value,
                'min_transaction' => $voucher->min_transaction,
            ],
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
        ], 200);
    }
}
