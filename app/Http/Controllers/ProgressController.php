<?php

namespace App\Http\Controllers;

use App\Models\QuizResult;
use App\Models\ViewSession;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function logSession(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'model_id'   => 'required|exists:models_3d,id',
            'used_ar'    => 'nullable|boolean',
        ]);

        ViewSession::create([
            'student_id' => $request->student_id,
            'model_id'   => $request->model_id,
            'used_ar'    => $request->boolean('used_ar'),
            'started_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function studentProgress($studentId)
    {
        $results  = QuizResult::where('student_id', $studentId)->get();
        $sessions = ViewSession::where('student_id', $studentId)->get();

        $avg = $results->count()
            ? round($results->avg(fn ($r) => $r->score / max($r->total_questions, 1)) * 100, 1)
            : 0;

        return response()->json([
            'student_id'         => (int) $studentId,
            'models_viewed'      => $sessions->count(),
            'ar_sessions'        => $sessions->where('used_ar', true)->count(),
            'quizzes_taken'      => $results->count(),
            'average_score_pct'  => $avg,
        ]);
    }
}
