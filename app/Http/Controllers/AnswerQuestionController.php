<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Models\AnswerQuestionModels;
use App\Models\Question;
use Illuminate\Support\Facades\DB;

class AnswerQuestionController extends Controller
{
    public function getQuestionBank() {
        $questions = Question::all();
        $questions->transform(function($q) {
            $q->question_image = $q->question_image ? asset('storage/' . $q->question_image) : null;
            $q->tip_easy = $q->tip_easy ? asset('storage/' . $q->tip_easy) : null;
            $q->tip_intermediate = $q->tip_intermediate ? asset('storage/' . $q->tip_intermediate) : null;
            $q->tip_advanced = $q->tip_advanced ? asset('storage/' . $q->tip_advanced) : null;
            return $q;
        });
        return response()->json([ 'status' => 'success', 'data' => $questions ]);
    }

    public function initializeUserAnswers(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|integer',
            'academic_level' => 'required|string',
            'chapter' => 'required|string',
            'difficulty' => 'required|string',
            'question_pool' => 'required|array',
            'answers' => 'nullable|array',
            'current_index' => 'required|integer',
            'status' => 'required|string'
        ]);

        $userAnswer = AnswerQuestionModels::updateOrCreate(
            [
                'user_id' => $data['user_id'],
                'academic_level' => $data['academic_level'],
                'chapter' => $data['chapter'],
                'difficulty' => $data['difficulty']
            ],
            $data
        );

        return response()->json([
            'success' => true,
            'round_id' => $userAnswer->id,
            'status' => $userAnswer->status,
            'question_pool' => $userAnswer->question_pool,
            'answers' => $userAnswer->answers,
            'current_index' => $userAnswer->current_index
        ]);
    }

    public function getNextUnfinishedRound(Request $request)
    {
        $user_id = $request->input('user_id');
        $academic_level = $request->input('academic_level');
        $chapter = $request->input('chapter');

        $round = AnswerQuestionModels::where('user_id', $user_id)
            ->where('academic_level', $academic_level)
            ->where('chapter', $chapter)
            ->where('status', 'in-progress')
            ->first();

        if ($round) {
            return response()->json([
                'status' => 'in-progress',
                'round_id' => $round->id,
                'academic_level' => $round->academic_level,
                'chapter' => $round->chapter,
                'difficulty' => $round->difficulty,
                'question_pool' => $round->question_pool,
                'answers' => $round->answers,
                'current_index' => $round->current_index
            ]);
        }

        $lastRound = AnswerQuestionModels::where('user_id', $user_id)
            ->where('academic_level', $academic_level)
            ->where('chapter', $chapter)
            ->latest('id')
            ->first();

        $difficulties = ['Easy', 'Intermediate', 'Advanced'];

        if ($lastRound && $lastRound->status === 'finished') {
            if ($lastRound->score >= 8) {
                $idx = array_search(ucfirst(strtolower($lastRound->difficulty)), $difficulties);
                if ($idx !== false && $idx < count($difficulties) - 1) {
                    return response()->json([
                        'status' => 'upgrade',
                        'next_difficulty' => $difficulties[$idx + 1]
                    ]);
                } else {
                    return response()->json([
                        'status' => 'chapter-finished'
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 'repeat',
                    'difficulty' => $lastRound->difficulty
                ]);
            }
        }

        return response()->json([
            'status' => 'start-new-round',
            'difficulty' => 'Easy'
        ]);
    }

    public function getRoundResult(Request $request)
    {
        $round_id = $request->input('round_id');
        $round = AnswerQuestionModels::find($round_id);

        if(!$round){
            return response()->json(['success'=>false, 'message'=>'Round not found'], 404);
        }

        $answers = $round->answers ?? [];
        $correctCount = collect($answers)->filter(fn($a)=>$a['is_correct'] ?? false)->count();

        return response()->json([
            'success' => true,
            'round_id' => $round_id,
            'correct_count' => $correctCount,
            'total' => count($answers)
        ]);
    }

    public function getChapterProgress(Request $request)
    {
        $userId = $request->input('user_id');
        $level = $request->academic_level;

        $chapters = DB::table('questions')
            ->where('academic_level', $level)
            ->select('chapter')
            ->distinct()
            ->pluck('chapter');

        $difficulties = ['Easy','Intermediate','Advanced'];
        $result = [];
        
        foreach($chapters as $chapter){
            $completedCount = 0;
            $currentDiff = 'Easy';
            
            foreach($difficulties as $difficulty){
                $round = DB::table('user_answers')
                ->where('user_id', $userId)
                ->where('academic_level', $level)
                ->where('chapter', $chapter)
                ->where('difficulty', $difficulty)
                ->latest('id')
                ->first();
                
                if($round){
                    if($round->status === 'finished') $completedCount++;
                    else $currentDiff = $round->difficulty; // 当前轮难度
                    }
                }
                
                $percent = count($difficulties) ? round(($completedCount / count($difficulties)) * 100) : 0;
                
                $result[] = [
                    'chapter' => $chapter,
                    'percent' => $percent,
                    'difficulty' => $currentDiff
                ];
            }
            return response()->json($result);
        }

    public function submitAnswer(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|integer',
            'round_id' => 'required|integer',
            'academic_level' => 'required|string',
            'chapter' => 'required|string',
            'difficulty' => 'required|string',
            'question_pool' => 'required|array',
            'answers' => 'required|array',
            'current_index' => 'required|integer',
            'status' => 'required|string',
        ]);

        $round = AnswerQuestionModels::find($data['round_id']);
        if (!$round) {
            return response()->json(['success'=>false,'message'=>'Round not found'], 404);
        }

        $correctCount = collect($data['answers'])
            ->values()
            ->filter(fn($a) => $a['is_correct'] ?? false)
            ->count();

        $isFinished = ($data['current_index'] + 1) >= count($data['question_pool']);

        $round->update([
            'question_pool' => $data['question_pool'],
            'answers' => $data['answers'],
            'current_index' => $data['current_index'],
            'status' => $isFinished ? 'finished' : $data['status'],
            'score' => $correctCount,
        ]);

        return response()->json([
            'success' => true,
            'round_id' => $round->id,
            'score' => $correctCount,
            'status' => $isFinished ? 'finished' : 'in-progress'
        ]);
    }

    public function initializeNextRound(Request $request)
    {
        $userId = $request->input('user_id');
        $level = $request->input('academic_level');
        $chapter = $request->input('chapter');
        $difficulty = $request->input('difficulty', 'Easy');

        $questions = Question::where('academic_level', $level)
            ->where('chapter', $chapter)
            ->where('difficulty', $difficulty)
            ->inRandomOrder()
            ->take(5)
            ->get();

        if ($questions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No questions found for this difficulty'
            ], 404);
        }

        $questionIds = $questions->pluck('id')->toArray();

        $round = AnswerQuestionModels::create([
            'user_id' => $userId,
            'academic_level' => $level,
            'chapter' => $chapter,
            'difficulty' => $difficulty,
            'status' => 'in-progress',
            'question_pool' => $questionIds,
            'answers' => [],
            'current_index' => 0,
            'score' => 0,
        ]);

        $questions->transform(function($q) {
            $q->question_image = $q->question_image ? asset('storage/' . $q->question_image) : null;
            $q->tip_easy = $q->tip_easy ? asset('storage/' . $q->tip_easy) : null;
            $q->tip_intermediate = $q->tip_intermediate ? asset('storage/' . $q->tip_intermediate) : null;
            $q->tip_advanced = $q->tip_advanced ? asset('storage/' . $q->tip_advanced) : null;
            return $q;
        });

        return response()->json([
            'success' => true,
            'round_id' => $round->id,
            'question_pool' => $questions
        ]);
    }
}
