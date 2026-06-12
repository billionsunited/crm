<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowupNotification extends Model
{
    protected $fillable = [
        'lead_id',
        'user_id',
        'title',
        'message',
        'redirect_url',
        'customer_name',
        'company_name',
        'follow_up_date',
        'is_triggered',
        'is_read',
    ];

    protected $casts = [
        'follow_up_date' => 'date',
        'is_triggered' => 'boolean',
        'is_read' => 'boolean',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
