<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alumni extends Model
{
    use HasFactory;

    protected $table = 'alumnis';

    protected $fillable = [
        'student_id',
        'nis',
        'name',
        'jurusan',
        'kelas_terakhir',
        'tahun_lulus',
        'tanggal_lulus',
        'alamat',
        'phone',
        'email',
        'keterangan',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }
}
