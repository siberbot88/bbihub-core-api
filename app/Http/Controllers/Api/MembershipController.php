<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\Workshop;
use Illuminate\Http\JsonResponse;

class MembershipController extends Controller
{
    /**
     * Get all active memberships for a workshop
     */
    public function index(Workshop $workshop): JsonResponse
    {
        $memberships = Membership::where('workshop_id', $workshop->id)
            ->active()
            ->orderBy('price')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $memberships,
        ]);
    }
    
    /**
     * Get membership details
     */
    public function show(Membership $membership): JsonResponse
    {
        $membership->load('workshop');
        
        return response()->json([
            'success' => true,
            'data' => $membership,
        ]);
    }
}
