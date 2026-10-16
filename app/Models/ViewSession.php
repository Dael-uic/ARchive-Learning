<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewSession extends Model
{
    public $timestamps = false;
    protected $fillable = ['student_id', 'model_id', 'started_at', 'used_ar'];
}
