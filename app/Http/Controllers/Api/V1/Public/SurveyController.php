<?php

namespace App\Http\Controllers\Api\v1\Public;

use App\Http\Controllers\Controller;
use App\Mail\CustomMail;
use App\Models\Survey;
use App\Models\SurveyResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SurveyController extends Controller
{
    public function __construct()
    {
        $this->middleware('throttle:2,30')->only(['storeAnswer']);
    }

    public function show(Survey $survey)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'survey' => $survey,
            ]
        ]);
    }

    public function storeAnswer(Request $request, Survey $survey)
    {
        $answers = $request->all();
        $text = "";
        foreach ($survey->questions as $idx => $question) {
            $answer = $answers[$idx];
            if (is_array($answer)) {
                $answer = implode(", ", $answer);
            }
            $text .= $question->title . ": " . $answer . "<br>";
        }
        $survey_result = SurveyResult::create([
            'survey_id' => $survey->id,
            'results' => $answers,
        ]);
        Mail::to(['saman.moayeri@gmail.com'])->send(
            new CustomMail(
                "پرسشنامه - " . $survey->title . __(" ") . $survey_result->id,
                $text
            )
        );
        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'نظرسنجی با موفقیت ثبت شد.',
            ]
        ]);
    }
}
