<?php

namespace App\Http\Controllers;

use App\Models\Model3D;
use App\Models\QuizQuestion;
use App\Models\QuizResult;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function addQuestion(Request $request, $modelId)
    {
        Model3D::findOrFail($modelId); // 404s automatically if the model doesn't exist

        $request->validate([
            'question_text'  => 'required|string',
            'option_a'       => 'required|string',
            'option_b'       => 'required|string',
            'option_c'       => 'required|string',
            'option_d'       => 'required|string',
            'correct_option' => 'required|in:A,B,C,D',
        ]);

        $question = QuizQuestion::create(array_merge(
            $request->only(['question_text', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_option']),
            ['model_id' => $modelId]
        ));

        return response()->json($question, 201);
    }

    public function questionsForModel($modelId)
    {
        // $hidden = ['correct_option'] on the model keeps the answer key out of this response
        return response()->json(QuizQuestion::where('model_id', $modelId)->get());
    }

    public function checkAnswer(Request $request, $questionId)
    {
        $request->validate(['option' => 'required|in:A,B,C,D,a,b,c,d']);
        $question = QuizQuestion::findOrFail($questionId);
        $isCorrect = strtoupper($request->option) === strtoupper($question->correct_option);

        return response()->json([
            'correct'        => $isCorrect,
            'correct_option' => $question->correct_option,
        ]);
    }

    public function submitResult(Request $request)
    {
        $request->validate([
            'student_id'       => 'required|exists:users,id',
            'model_id'         => 'required|exists:models_3d,id',
            'score'            => 'required|integer',
            'total_questions'  => 'required|integer',
            'duration_seconds' => 'required|numeric',
        ]);

        QuizResult::create(array_merge($request->all(), ['submitted_at' => now()]));

        return response()->json(['ok' => true, 'score' => $request->score, 'total' => $request->total_questions]);
    }
}
