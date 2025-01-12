<?php

namespace App\Http\Controllers;

// use Auth;
// use Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Validator;
use DateTime;
use Illuminate\Http\Request;
use App;
use App\Model\User;

class GameController extends Controller
{
    public $successStatus = 200;
    public $Encryption;
    public $Helper;

    public function __construct(MyEncryption $MyEncryption, Helper $Helper, SpinManager $SpinManager)
    {
        $this->Encryption = $MyEncryption;
        $this->Helper = $Helper;
        $this->SpinManager = $SpinManager;
        // $this->middleware(['auth:web']);
    }

    /* ----------- API RELATED ---------- */

    public function spin_api(Request $request){ 
        $end = $this->Helper->campaign_end();

        if(!$end){
            $user = Auth::guard('web')->user();
            if ($user) {
                // chance to spin
                if($user->chance > 0){
                    $spin_id = $this->SpinManager->SpinExist($user->id);
                    if($spin_id < 0){
                        $spin_id = $this->SpinManager->SpinPlaceholder($user->id);
                    };

                    // delay for a short while
                    sleep(2);
                };

                $result = false;
                if($spin_id > 0){
                    // get the prize
                    $result = $this->SpinManager->updateSpinRecord($spin_id, $user->id);
                };

                $thisPrize = null;
                if($result){
                    $thisPrize = $this->SpinManager->getSpinResult($spin_id, $user->id);
                    $thisImg = $this->SpinManager->getImgResult($spin_id, $user->id);
                };



                // params to pass for callback
                $params = [
                    'prize' => $thisPrize,
                    'chance' => $user->chance,
                    'max_chance' => $user->max_chance,
                    'prize_image' => $thisImg,
                ];

                // return response()->json($params, 200);
                $result = $this->Encryption->package($params);
                return response()->json(['result' => $result], 200);
            };
        };

        return response()->json(['error' => 'time submission failed.'], 200);
    }

    public function prizeData() {
        $data = [
            ['id' => 1, 'name' => 'Q1'],
            ['id' => 2, 'name' => 'Q2'],
            ['id' => 3, 'name' => 'Q3'],
            ['id' => 4, 'name' => 'Q4'],
            ['id' => 5, 'name' => 'Q5'],
            ['id' => 6, 'name' => 'Q6'],
            ['id' => 7, 'name' => 'Q7'],
            ['id' => 8, 'name' => 'Q8'],
            ['id' => 9, 'name' => 'Q9'],
            ['id' => 10, 'name' => 'Q10']
        ];

        // return $data;
        return response()->json(['prizeData' => $data], 200);
    }
}
