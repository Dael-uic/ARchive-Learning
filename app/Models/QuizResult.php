<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizResult extends Model
{
    public $timestamps = false;
    protected $fillable = ['student_id', 'model_id', 'score', 'total_questions', 'duration_seconds', 'submitted_at'];
}
