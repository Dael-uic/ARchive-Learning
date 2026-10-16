<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Model3D extends Model
{
    protected $table = 'models_3d';
    protected $fillable = ['title', 'description', 'category', 'filename', 'uploader_id'];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    public function questions()
    {
        return $this->hasMany(QuizQuestion::class, 'model_id');
    }
}
