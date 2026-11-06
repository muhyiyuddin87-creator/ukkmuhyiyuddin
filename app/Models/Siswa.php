<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswas';

    protected $fillable = [
        'name',
        'nis',
        'kelas',
        'jurusan',
        'alamat',
        'phone',
        'email',
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'student_id');
    }
}
