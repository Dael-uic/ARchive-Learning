<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role'];
    protected $hidden = ['password'];

    public function uploadedModels()
    {
        return $this->hasMany(Model3D::class, 'uploader_id');
    }

    public function quizResults()
    {
        return $this->hasMany(QuizResult::class, 'student_id');
    }

    public function viewSessions()
    {
        return $this->hasMany(ViewSession::class, 'student_id');
    }
}
