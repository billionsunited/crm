<?php

namespace App\Observers;

use App\Models\Lead;
use App\Models\FollowupNotification;
use Carbon\Carbon;

class LeadObserver
{
    /**
     * Handle the Lead "created" event.
     */
    public function created(Lead $lead): void
    {
        // Notification logic moved to cron (ProcessFollowupNotifications)
    }

    /**
     * Handle the Lead "updated" event.
     */
    public function updated(Lead $lead): void
    {
        // Notification logic moved to cron (ProcessFollowupNotifications)
    }


    /**
     * Handle the Lead "deleted" event.
     */
    public function deleted(Lead $lead): void
    {
        FollowupNotification::where('lead_id', $lead->id)->delete();
    }

    /**
     * Handle the Lead "restored" event.
     */
    public function restored(Lead $lead): void
    {
        //
    }

    /**
     * Handle the Lead "force deleted" event.
     */
    public function forceDeleted(Lead $lead): void
    {
        FollowupNotification::where('lead_id', $lead->id)->delete();
    }
}
