<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendances';

    protected $fillable = [
        'journal_id',
        'student_id',
        'status',
        'photo_path',
        'notes',
        'date',
    ];

    public function journal()
    {
        return $this->belongsTo(Journal::class, 'journal_id');
    }

    public function student()
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }
}
