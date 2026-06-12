<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FollowupNotification;

class NotificationController extends Controller
{
    /**
     * Display a listing of the notifications.
     */
    public function index(Request $request)
    {
        $query = FollowupNotification::query();


        // Apply Filters
        if ($request->filled('date')) {
            $query->whereDate('follow_up_date', $request->date);
        }
        
        if ($request->filled('customer')) {
            $query->where('customer_name', 'like', '%' . $request->customer . '%');
        }

        if ($request->filled('company')) {
            $query->where('company_name', 'like', '%' . $request->company . '%');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('message', 'like', '%' . $search . '%');
            });
        }

        $notifications = $query->orderBy('follow_up_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Fetch unread triggered notifications for the header bell icon.
     */
    public function fetch()
    {
        $query = FollowupNotification::where('is_read', 0);


        $notifications = $query->orderBy('follow_up_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $count = $query->count();

        return response()->json([
            'count' => $count,
            'notifications' => $notifications
        ]);
    }

    /**
     * Mark a notification as read and return redirect URL.
     */
    public function markAsRead($id)
    {
        $notification = FollowupNotification::findOrFail($id);


        $notification->update(['is_read' => 1]);

        return redirect()->to(url($notification->redirect_url));
    }
}
