<?php

namespace App\Services;

use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VehicleService
{
    /**
     * Membuat kendaraan baru dengan kode unik yang aman.
     */
    public function createVehicle(array $validatedData, ?string $include = null): Vehicle
    {
        $vehicle = DB::transaction(function () use ($validatedData) {
            $modelSegment = $this->modelSegment($validatedData['model']);
            $prefix = "VH-{$modelSegment}-";

            $last = Vehicle::where('code', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderBy('code', 'desc')
                ->first();

            $next = 1;
            if ($last && preg_match('/^' . preg_quote($prefix, '/') . '(\d{3})$/', $last->code, $m)) {
                $next = ((int) $m[1]) + 1;
            }
            $code = $prefix . str_pad((string)$next, 3, '0', STR_PAD_LEFT);

            return Vehicle::create(array_merge($validatedData, [
                'id'   => (string) Str::uuid(),
                'code' => $code,
            ]));
        });

        if ($include === 'customer') {
            $vehicle->loadMissing('customer:id,name');
        }

        return $vehicle;
    }

    /**
     * Memperbarui kendaraan, dengan opsi regenerasi kode.
     */
    public function updateVehicle(Vehicle $vehicle, array $validatedData): Vehicle
    {
        if (($validatedData['regenerate_code'] ?? false) && !empty($validatedData['model'])) {

            DB::transaction(function () use (&$vehicle, $validatedData) {
                $vehicle->fill($validatedData);
                $vehicle->save();

                $modelSegment = $this->modelSegment($vehicle->model);
                $prefix = "VH-{$modelSegment}-";

                $last = Vehicle::where('code', 'like', $prefix . '%')
                    ->lockForUpdate()
                    ->orderBy('code', 'desc')
                    ->first();

                $next = 1;
                if ($last && preg_match('/^' . preg_quote($prefix, '/') . '(\d{3})$/', $last->code, $m)) {
                    $next = ((int) $m[1]) + 1;
                }

                $vehicle->code = $prefix . str_pad((string)$next, 3, '0', STR_PAD_LEFT);
                $vehicle->save(); // Simpan kode baru
            });
        } else {
            $vehicle->fill($validatedData);
            $vehicle->save();
        }

        return $vehicle;
    }

    /**
     * Ubah model jadi segmen code: uppercase + hanya A-Z0-9.
     */
    private function modelSegment(string $model): string
    {
        $ascii = Str::ascii($model);
        $upper = Str::upper($ascii);
        return preg_replace('/[^A-Z0-9]/', '', $upper) ?: 'GEN';
    }
}
