<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Workshop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get user's workshops
        $workshopIds = [];
        if ($user->hasRole('owner')) {
            $workshopIds = $user->workshops->pluck('id');
        } elseif ($user->employment) {
            $workshopIds = [$user->employment->workshop_id];
        }

        // Base Query: Feedback where transaction -> workshop is in list
        $query = Feedback::whereHas('transaction', function ($q) use ($workshopIds) {
            $q->whereIn('workshop_uuid', $workshopIds);
        })->with(['transaction.customer', 'transaction.service']);

        // Filter by Star
        if ($request->has('filter') && in_array($request->filter, ['1', '2', '3', '4', '5'])) {
            $query->where('rating', $request->filter);
        }

        // Calculate Stats
        // Note: For large datasets, caching these stats is recommended.
        $allFeedback = Feedback::whereHas('transaction', function ($q) use ($workshopIds) {
            $q->whereIn('workshop_uuid', $workshopIds);
        })->get();

        $total = $allFeedback->count();
        $avg = $total > 0 ? $allFeedback->avg('rating') : 0;

        $distribution = [
            '5' => $allFeedback->where('rating', 5)->count(),
            '4' => $allFeedback->where('rating', 4)->count(),
            '3' => $allFeedback->where('rating', 3)->count(),
            '2' => $allFeedback->where('rating', 2)->count(),
            '1' => $allFeedback->where('rating', 1)->count(),
        ];

        // Pagination
        $reviews = $query->latest('submitted_at')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'average' => round($avg, 1),
                    'total' => $total,
                    'distribution' => $distribution
                ],
                'reviews' => $reviews
            ]
        ]);
    }
}
