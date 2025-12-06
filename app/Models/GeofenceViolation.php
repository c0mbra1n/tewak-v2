<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeofenceViolation extends Model
{
    protected $fillable = [
        'user_id',
        'schedule_id',
        'class_name',
        'teacher_lat',
        'teacher_lng',
        'class_lat',
        'class_lng',
        'distance',
        'radius',
        'is_read',
    ];

    protected $casts = [
        'teacher_lat' => 'decimal:7',
        'teacher_lng' => 'decimal:7',
        'class_lat' => 'decimal:7',
        'class_lng' => 'decimal:7',
        'distance' => 'decimal:2',
        'radius' => 'decimal:2',
        'is_read' => 'boolean',
    ];

    /**
     * Get the teacher who violated geofence
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the schedule
     */
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}
