<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeTiming extends Model
{
    use HasFactory;

    protected $fillable = [
        'day_of_week',
        'is_working_day',
        'start_time',
        'end_time',
    ];
}
