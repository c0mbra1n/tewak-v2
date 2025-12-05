<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'full_name',
        'role',
        'subject',
        'class_id',
        'password',
        'photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the class assigned to this user (for admin_kelas)
     */
    public function assignedClass()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    /**
     * Check if user is admin_kelas
     */
    public function isAdminKelas(): bool
    {
        return $this->role === 'admin_kelas';
    }

    /**
     * Get subjects that user teaches (many-to-many)
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'user_subjects');
    }

    /**
     * Check if user is guru
     */
    public function isGuru(): bool
    {
        return $this->role === 'guru';
    }
}
