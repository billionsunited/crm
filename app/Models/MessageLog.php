<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageLog extends Model
{
    protected $fillable = [
        'lead_id',
        'campaign_lead_id',
        'type',
        'to_number',
        'template_name',
        'parameters',
        'status',
        'api_response',
    ];

    protected $casts = [
        'parameters' => 'array',
        'api_response' => 'array',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function campaignLead()
    {
        return $this->belongsTo(CampaignLead::class);
    }
}
