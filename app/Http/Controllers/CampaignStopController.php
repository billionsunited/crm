<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CampaignStopController extends Controller
{
    /**
     * Set a cache flag to stop any running campaigns for the authenticated user.
     */
    public function stop(Request $request)
    {
        $userId = auth()->id();
        if ($userId) {
            // Set a flag that expires in 10 minutes (600 seconds)
            Cache::put('stop_campaign_' . $userId, true, 600);
            
            return response()->json([
                'success' => true,
                'message' => 'Stop signal sent successfully. The campaign will halt shortly.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unauthorized'
        ], 401);
    }
}
