<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Validator;
use DateTime;
use DateInterval;
use Illuminate\Http\Request;
use App;
use App\Model\SurveyEntry;
use App\Model\Question;

class SurveyController extends Controller
{
    public $successStatus = 200;
    public $Helper;
    private $questionLimit = 6;  //14

    public function __construct(Helper $Helper)
    {
        $this->Helper = $Helper;
        // $this->middleware(['auth:web']);
    }

    public function callAction($method, $parameters)
    {
        return parent::callAction($method, array_values($parameters));
    }

    public function index(Request $request)
    {
        // if(!$this->Helper->isStaging()){
            // return redirect()->route('home.dashboard');
        // };

        $BaseUrl = url('/');
        $week = 1; //$this->Helper->getWeek();

        $params = [
            'BaseUrl' => $BaseUrl,
            'week' => $week
        ];

        return view("survey.index", $params);
    }

    public function survey(Request $request) {
        $BaseUrl = url('/');
        $user = Auth::guard('web')->user();
        if (!$user) {
            return redirect()->route('home.login');
        };

        $end = $this->Helper->campaign_end();
        if($end) {
            return redirect()->route('home.dashboard');
        };

        // check if user completed survey
        // $complete = SurveyEntry::where('user_id', $user['id'])->where('status', 1)->first();
        // if($complete){
        //     return redirect()->route('survey.result');
        //     // return redirect()->route('home.dashboard');
        // };

        if(!$request->question_number){
            // redirect...
            return redirect()->route('survey.survey', ['question_number' => 1]);
        };
        if($request->question_number > $this->questionLimit){
            // return redirect()->route('survey.survey', ['question_number' => $this->questionLimit]);
            return redirect()->route('survey.result');
        };

        if($request->question_number != 1){
            // check if has valid quiz session
            $survey_id = $request->session()->get('survey_id');
            if(!$survey_id) {
                return redirect()->route('survey.survey', ['question_number' => 1]);
            } else {
                // has session, check if current page matches progress
                $entry = SurveyEntry::where('id', $survey_id)
                        ->first();
                $progress = $this->SurveyProgress($entry);
                $question_number = ($progress) ? substr($progress, 1) : 1;
                if($request->question_number != $question_number){
                    // redirect
                    return redirect()->route('survey.survey', ['question_number' => $question_number]);
                };
            };
        };

        //set multiple choice  
        $thisQuestion = Question::select(['question', 'a_1', 'a_2', 'a_3', 'a_4'])->where('id', $request->question_number)->first();
        $question = $thisQuestion['question'];
        $answerList = [];

        if($thisQuestion['a_1']){
            array_push($answerList, $thisQuestion['a_1']);
        };
        if($thisQuestion['a_2']){
            array_push($answerList, $thisQuestion['a_2']);
        };
        if($thisQuestion['a_3']){
            array_push($answerList, $thisQuestion['a_3']);
        };
        if($thisQuestion['a_4']){
            array_push($answerList, $thisQuestion['a_4']);
        };

        $params = [
            'BaseUrl' => $BaseUrl,
            'question' => 'trivia.'.$question,
            'answerList' => $answerList,
            'labelList' => ['A', 'B', 'C', 'D'],
            'next' => $request->question_number+1,
            // 'back' => $request->question_number-1,
        ];

        return view("survey.survey", $params);
    }

    public function result(Request $request){
        $end = $this->Helper->campaign_end();
        if($end) {
            return redirect()->route('home.dashboard');
        };
        
        $BaseUrl = url('/');
        // $entry_id = $request->session()->get('survey_id');
        $user = Auth::guard('web')->user();
        $entry = SurveyEntry::where('user_id', $user->id)->where('status', 1)->first();
        if(!$entry){
            $entry = SurveyEntry::where('user_id', $user->id)->orderBy('created_at', 'desc')->first();
        };

        // check if user completed survey
        // $complete = SurveyEntry::where('user_id', $user['id'])->where('status', 1)->first();
        // if($complete){
        //     return redirect()->route('survey.result');
        //     // return redirect()->route('home.dashboard');
        // }
        
        $params = [
            'BaseUrl' => $BaseUrl,
            'score' => $entry->score,
            'spin_chance' =>  $user->chance
        ];
        return view('survey.survey_result', $params);
    }

    /* ----------- API RELATED ---------- */
    
    public function surveySession_api(Request $request) {
        $user = Auth::guard('web')->user();
        if($user){
            // create new entry and record the first time
            $p_param = [
                'user_id' => $user['id'],
                'trackip' => $_SERVER['REMOTE_ADDR'],
                'status' => 0
            ];
            $entry = SurveyEntry::create($p_param);
            $request->session()->put('survey_id', $entry['id']);
            
            // params to pass for callback
            $params = [
                'result' => 1
            ];

            return response()->json($params, 200);
        };
    }

    public function survey_api(Request $request) {
        // check if entry id exist
        $entry_id = $request->session()->get('survey_id');
        $result = false;
        if($entry_id){
            // entry exist, get current entry
            $entry = SurveyEntry::where('id', $entry_id)
                        ->first();
            // get entry progress
            $progress = $this->SurveyProgress($entry);
            $answer = Question::where('id', substr($progress, 1))->first()['answer']; //$this->QuizAnswer($progress, $request->answer);
            // check if answer is correct
            $score = $entry->score;
            if($answer == $request->answer){
                $score += 1;
            };
            // update entry
            $now = new DateTime();
            $update_entry = SurveyEntry::where('id', $entry_id)
                        ->update([$progress => $request->answer, $progress.'_updated' => $now, 'score' => $score]);

            if($update_entry){
                $result = $answer;
            };

            if($progress == 'Q'.$this->questionLimit){
                // end of quiz
                // mark quiz done
                // $complete_entry = SurveyEntry::where('id', $entry_id)
                        // ->update(['status' => 1]);
                // recalculate score and mark quiz done
                $this->recalculateScore($entry_id);
                // remove quiz session id
                $request->session()->put('entry_id', null);
            };
        };
        
        // params to pass for callback
        $params = [
            'result' => $result
        ];

        return response()->json($params, 200);
    }

    private function recalculateScore($entry_id){
        $thisScore = 0;
        $thisEntry = SurveyEntry::where('id', $entry_id)->first();
        $questionList = Question::all();

        if($thisEntry->Q1 == $questionList[0]->answer){
            $thisScore += 1;
        };
        if($thisEntry->Q2 == $questionList[1]->answer){
            $thisScore += 1;
        };
        if($thisEntry->Q3 == $questionList[2]->answer){
            $thisScore += 1;
        };
        if($thisEntry->Q4 == $questionList[3]->answer){
            $thisScore += 1;
        };
        if($thisEntry->Q5 == $questionList[4]->answer){
            $thisScore += 1;
        };
        if($thisEntry->Q6 == $questionList[5]->answer){
            $thisScore += 1;
        };
        // if($thisEntry->Q7 == $questionList[6]->answer){
        //     $thisScore += 1;
        // };
        // if($thisEntry->Q8 == $questionList[7]->answer){
        //     $thisScore += 1;
        // };
        // if($thisEntry->Q9 == $questionList[8]->answer){
        //     $thisScore += 1;
        // };
        // if($thisEntry->Q10 == $questionList[9]->answer){
        //     $thisScore += 1;
        // };
        // if($thisEntry->Q11 == $questionList[10]->answer){
        //     $thisScore += 1;
        // };
        // if($thisEntry->Q12 == $questionList[11]->answer){
        //     $thisScore += 1;
        // };
        // if($thisEntry->Q13 == $questionList[12]->answer){
        //     $thisScore += 1;
        // };
        // if($thisEntry->Q14 == $questionList[13]->answer){
        //     $thisScore += 1;
        // };

        // update entry
        $status = ($thisScore >= 5) ? 1 : 0;
        $update_entry = SurveyEntry::where('id', $entry_id)
                    ->update(['score' => $thisScore, 'status' => $status]);
    }

    private function SurveyProgress($entryRef){ // progress checker
        $thisProgress = null;
        if($entryRef['Q1'] == -1){
            $thisProgress = 'Q1';
        } else if ($entryRef['Q2'] == -1){
            $thisProgress = 'Q2';
        } else if ($entryRef['Q3'] == -1){
            $thisProgress = 'Q3';
        } else if ($entryRef['Q4'] == -1){
            $thisProgress = 'Q4';
        } else if ($entryRef['Q5'] == -1){
            $thisProgress = 'Q5';
        } else if ($entryRef['Q6'] == -1){
            $thisProgress = 'Q6';
        }
        //  else if ($entryRef['Q7'] == -1){
        //     $thisProgress = 'Q7';
        // } else if ($entryRef['Q8'] == -1){
        //     $thisProgress = 'Q8';
        // } else if ($entryRef['Q9'] == -1){
        //     $thisProgress = 'Q9';
        // } else if ($entryRef['Q10'] == -1){
        //     $thisProgress = 'Q10';
        // } else if ($entryRef['Q11'] == -1){
        //     $thisProgress = 'Q11';
        // } else if ($entryRef['Q12'] == -1){
        //     $thisProgress = 'Q12';
        // } else if ($entryRef['Q13'] == -1){
        //     $thisProgress = 'Q13';
        // } else if ($entryRef['Q14'] == -1){
        //     $thisProgress = 'Q14';
        // };/* else if ($entryRef['Q15'] == -1){
        //     $thisProgress = 'Q15';
        // };*/

        return $thisProgress;
    }
}