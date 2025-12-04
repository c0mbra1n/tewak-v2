<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'class_id',
        'day',
        'subject',
        'start_time',
        'end_time',
        'lesson_hours',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    /**
     * Get calculated end time based on lesson hours (45 min each)
     */
    public function getCalculatedEndTimeAttribute()
    {
        return \Carbon\Carbon::parse($this->start_time)->addMinutes($this->lesson_hours * 45);
    }

    /**
     * Get late threshold (start_time + 15 minutes)
     */
    public function getLateThresholdAttribute()
    {
        return \Carbon\Carbon::parse($this->start_time)->addMinutes(15);
    }
}
