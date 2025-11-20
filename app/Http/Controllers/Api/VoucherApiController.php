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
}
