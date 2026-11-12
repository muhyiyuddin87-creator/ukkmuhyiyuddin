<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    public $timestamps = false;

    protected $fillable = [
        'role_id',
        'parent_id',
        'class_id',
        'student_id',
        'username',
        'email',
        'password',
        'fullname',
        'phone',
        'nip',
        'is_active',
        'must_change_password',
        'password_reset_at',
        'created_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'role_id' => 'integer',
        'parent_id' => 'integer',
        'class_id' => 'integer',
        'student_id' => 'integer',
        'is_active' => 'boolean',
        'must_change_password' => 'boolean',
        'password_reset_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function getAuthIdentifierName()
    {
        return 'username';
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'teacher_id');
    }

    public function student()
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }

    public function getWaliClasses(): array
    {
        $classes = \Illuminate\Support\Facades\DB::table('user_school_classes')
            ->join('school_classes', 'user_school_classes.school_class_id', '=', 'school_classes.id')
            ->where('user_school_classes.user_id', $this->id)
            ->pluck('school_classes.name')
            ->values()
            ->all();

        if (empty($classes) && $this->class_id) {
            $single = \Illuminate\Support\Facades\DB::table('school_classes')->where('id', $this->class_id)->value('name');
            if ($single) {
                return [$single];
            }
        }

        return $classes;
    }

    public function isWaliKelas(): bool
    {
        if ((int) $this->role_id === 5) {
            return true;
        }

        if ((int) $this->role_id === 2) {
            if ($this->class_id) {
                return true;
            }

            return \Illuminate\Support\Facades\DB::table('user_school_classes')
                ->where('user_id', $this->id)
                ->exists();
        }

        return false;
    }
}
