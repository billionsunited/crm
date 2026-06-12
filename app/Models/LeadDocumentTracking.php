<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadDocumentTracking extends Model
{
    protected $fillable = ['lead_id', 'document_name'];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
