<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

/**
 * Trait untuk menstandardisasi respon API JSON
 */
trait ApiResponseTrait
{
    /**
     * Respon sukses terstandardisasi.
     *
     * @param string $message
     * @param mixed $data
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse(string $message, $data = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    /**
     * Respon error terstandardisasi.
     *
     * @param string $message Pesan error utama.
     * @param int $code Kode status HTTP.
     * @param mixed $errors Detail error (bisa string atau array, opsional).
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse(string $message, int $code = 400, $errors = null): JsonResponse
    {
        $response = [
            'message' => $message,
        ];

        if ($errors) {
            $errorKey = is_array($errors) ? 'errors' : 'error';
            $response[$errorKey] = $errors;
        }

        return response()->json($response, $code);
    }
}
