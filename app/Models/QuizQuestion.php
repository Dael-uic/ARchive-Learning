<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'model_id', 'question_text', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_option',
    ];

    // Never expose correct_option to students — same guard as the FastAPI schema.QuizQuestionOut
    protected $hidden = ['correct_option'];

    public function model3d()
    {
        return $this->belongsTo(Model3D::class, 'model_id');
    }
}
