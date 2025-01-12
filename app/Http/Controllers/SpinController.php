<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Model\Prize;
use App\Model\Spin;
use App\Model\Tng;
use App\Model\EmailLog;
use App\Model\Trackings;
/*
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
*/
// use Illuminate\Support\Facades\Log;

class SpinController extends Controller
{
    public $Helper;

    public function __construct(Helper $Helper)
    {
        $this->Helper = $Helper;
    }

    public function tng(Request $request) {
        $BaseUrl = url('/');
        $user = Auth::guard('web')->user();
        if (!$user) {
            return redirect()->route('home.login');
        };
        // $chance = $user->chance;
        // $available = $user->available;
        $value = 0;
        $tng_id = 0;
        $tng_pin = 'XXXXX-XXXXX-XXXXX';
        $week = $this->Helper->getWeek();
        $prize = null;

        // if user is entitled to get tng
        $entitled = $request->session()->get('entitled');
        if($entitled != 1){
            return redirect()->route('tng.thankyou');
        };
        // get previous
        $status = 1;
        $previous = $request->session()->get('previous');
        if($previous == 'game'){
            // do nothing, status already is 1
        } else {
            $status = 2;
        };

        // Log::info('tng spin, status: '.$status);

        // temp disable for unlimited spin
        
        // check if tng record already exist today
        $today = date("Y-m-d");
        $today_start = $today." 00:00:00";
        $today_end = $today." 23:59:59";
        if($status == 1){
            $tng_today = Spin::where('user_id', $user->id)->where('status', $status)
                        ->where('created_at', '>=', $today_start)->where('created_at', '<=', $today_end)->first();
        } else if($status == 2) {
            // get scope
            $scope = $request->session()->get('scope');
            // change dates to campaign period
            $campaign_start = '2024-04-01 00:00:00';
            $tng_today = Spin::where('user_id', $user->id)->where('status', $status)->where('scope', $scope)//->get()->count();
                        ->where('created_at', '>=', $campaign_start)->where('created_at', '<=', $today_end)->first();
        };
        
        // $tng_today = false;
        if($tng_today){
            // Log::info('has tng_today');

            // display the last obtained tng pin
            $this_tng = Tng::where('id', $tng_today->tng_id)->first();
            if($this_tng){
                $tng_pin = $this_tng->pin;
                $value = $this_tng->value;
            } else {
                $value = -1;
            };
        };/* else {
            // Log::info('no tng_today');

            // check if user is spam
            $is_spam = false;
            $this_ip = $_SERVER['REMOTE_ADDR'];
            $total_spin = Spin::where('trackip', $this_ip)
                            ->where('created_at', '>=', $today_start)->where('created_at', '<=', $today_end)->get()->count();
            if($total_spin >= 6) $is_spam = true;
            // spin start
            // if($entitled){
                if($previous == 'game'){
                    if($chance > 0){
                        // deduct user chance
                        $chance = $chance-1;
                        $user->chance = $chance;
                        $user->save();

                        // get prize tng pin value from RM1~RM15
                        $prize = $this->getPrize($is_spam);
                        // temporary force all game to distribute RM0.50 tng
                        // $prize = $this->getPrize(true);
                        // $request->session()->put('prize_id', $prize);
                        // $value = 0;
                        if($prize == 5){
                            $value = 0.5;
                        } else if($prize == 4){
                            $value = 1;
                        } else if($prize == 3){
                            $value = 3;
                        } else if($prize == 6){
                            $value = 5;
                        } else if($prize == 2){
                            $value = 10;
                        } else if($prize == 1){
                            $value = 15;
                        };
                    };
                } else if($previous == 'profile'){
                    if($available > 0){
                        // deduct task complete available count
                        $available = $available-1;
                        $user->available = $available;
                        $user->save();

                        /*
                        // prize tng pin value RM5 only
                        $prize = 3;
                        $value = 3;

                        // deduct prize quantity
                        $thisPrize = Prize::where('id', 3)->first();
                        $thisPrize->quantity = $thisPrize->quantity - 1;
                        $thisPrize->save();
                        *//*

                        // get prize tng pin value from RM1~RM15
                        $prize = $this->getPrize($is_spam);
                        // $request->session()->put('prize_id', $prize);
                        // $value = 0;
                        if($prize == 5){
                            $value = 0.5;
                        } else if($prize == 4){
                            $value = 1;
                        } else if($prize == 3){
                            $value = 3;
                        } else if($prize == 6){
                            $value = 5;
                        } else if($prize == 2){
                            $value = 10;
                        } else if($prize == 1){
                            $value = 15;
                        };
                    };
                } else {
                    // Log::info('preious: calendar');
                    // Log::info('available: '.$available);
                    if($available > 0){
                        // deduct task complete available count
                        $available = $available-1;
                        $user->available = $available;
                        $user->save();

                        // temporary disable, randomize as quantity are becoming lower than expected
                        
                        if(!$is_spam){
                            // prize tng pin value RM10 only
                            $prize = 2;

                            $thisPrize = Prize::where('id', 2)->first();
                            if($thisPrize->quantity > 0){
                                // deduct prize quantity
                                $thisPrize->quantity = $thisPrize->quantity - 1;
                                $thisPrize->save();
                            } else {
                                // oh no, no more RM10 tng
                                $prize = $this->getPrize($is_spam);
                            };
                        } else {
                            // possible spammer, get prize tng pin value from RM0.50
                            $prize = $this->getPrize($is_spam);
                        };

                        // get prize tng pin value from RM1~RM15
                        // $prize = $this->getPrize($is_spam);
                        
                        // $value = 0;
                        if($prize == 5){
                            $value = 0.5;
                        } else if($prize == 4){
                            $value = 1;
                        } else if($prize == 3){
                            $value = 3;
                        } else if($prize == 6){
                            $value = 5;
                        } else if($prize == 2){
                            $value = 10;
                        } else if($prize == 1){
                            $value = 15;
                        };
                    };
                };

                if(isset($prize)){
                    // get allowed tng list
                    $tngList = Tng::where('value', $value)->where('type', '>', 2)->where('status', 1)->limit(100)->get();
                    // tng randomization
                    $max = count($tngList);
                    if($max > 0){
                        $thisIndex = mt_rand(1, $max);
                        $tng = $tngList[$thisIndex-1];

                        // mark tng as used
                        $tngUsed = Tng::where('id', $tng->id)->update(['status' => 0]);
                        $tng_id = $tng->id;
                        $tng_pin = $tng->pin;
                    };

                    $scope = null;
                    if($status == 2) $scope = $request->session()->get('scope');

                    // create spin record
                    $param = [
                        'user_id' => $user->id,
                        'prize_id' => $prize,
                        'tng_id' => $tng_id,
                        'scope' => $scope,
                        'trackip' => $_SERVER['REMOTE_ADDR'],
                        'status' => $status
                    ];
                    $spin_record = Spin::create($param);

                    // record session for returning visit purpose, just in case
                    $request->session()->put('pin', $tng_pin);
                    $request->session()->put('pin_value', $value);
                };
            // } else {
                // return redirect()->route('tng.thankyou');
            // };
        };*/

        if($prize == 10) $value = -1;

        // send notify email
        if($prize && $prize != 10) $this->notify_email($prize);

        $params = [
            'BaseUrl' => $BaseUrl,
            'tng_pin' => $tng_pin,
            'value' => $value,
            'week' => $week,
            // 'chance' => $chance,
            // 'prize' => $prize
        ];
        
        return view("tng.congratulations", $params);
    }
/*
    public function ip_debug(Request $request) {
        $today = date("Y-m-d");
        $today_start = $today." 00:00:00";
        $today_end = $today." 23:59:59";
        $this_ip = Trackings::where('ip_address', $_SERVER['REMOTE_ADDR'])->orderBy('id', 'DESC')->first();
        $this_uuid = $this_ip->uuid;
        $ip_list = Trackings::where('uuid', $this_uuid)
                    ->where('created_at', '>=', $today_start)->where('created_at', '<=', $today_end)
                    ->groupBy('ip_address')->pluck('ip_address')->toArray();

        // $test = Trackings::whereIn('ip_address', $ip_list)
                    // ->groupBy('ip_address')->pluck('ip_address')->toArray();

        if (in_array($_SERVER['REMOTE_ADDR'], $ip_list)){
            // do nothing
        } else {
            // pump in self
            array_push($_SERVER['REMOTE_ADDR'], $ip_list);
        };

        return print_r($ip_list);
    }

    public function session_debug(Request $request) {
        // $session_id = session()->getIp(); // session()->getId();
        // get current session ip
        $ip = DB::table('sessions')->where('id', session()->getId())->first()->ip_address;

        // get all session under same ip
        $session_group = DB::table('sessions')->where('ip_address', $ip)
                            ->groupBy('id')->pluck('id')->toArray();
        // get all ip from related session above
        $session_ip = DB::table('sessions')->whereIn('id', $session_group)
                            ->groupBy('ip_address')->pluck('ip_address')->toArray();
        // get all user who is under same ip
        $user_group = DB::table('sessions')->where('ip_address', $session_ip)
                        ->groupBy('user_id')->pluck('user_id')->toArray();
        $ip_group = DB::table('sessions')->whereIn('user_id', $user_group)
                        ->groupBy('ip_address')->pluck('ip_address')->toArray();
        $final_group = DB::table('sessions')->whereIn('ip_address', $ip_group)
                        ->groupBy('user_id')->pluck('user_id')->toArray();

        for($i=0; $i<count($final_group); $i++){
            // get cache
            $uuid = Cache::get('uuid_'.$final_group[$i]);
            if($uuid){
                // cache is found, end loop
                break;
            };
        };

        $user = Auth::guard('web')->user();
        if($uuid){
            // apply the same uuid to this user
            $uuid = Cache::put('uuid_'.$user->id, $uuid, 86400);
        } else {
            // assign new uuid
            $uuid = Cache::remember('uuid_'.$user->id, 86400, function(){
                return Str::uuid();
            });
        };

        return "<pre>".print_r($ip_group, true)."\n".print_r($final_group, true)."</pre>";
    }
*/
    public function thankyou(Request $request) {
        $BaseUrl = url('/');
        $user = Auth::guard('web')->user();
        if (!$user) {
            return redirect()->route('home.login');
        };
        
        $value = 0;
        $tng_pin = 'XXXXX-XXXXX-XXXXX';
        $week = $this->Helper->getWeek();

        $params = [
            'BaseUrl' => $BaseUrl,
            'tng_pin' => $tng_pin,
            'value' => $value,
            'week' => $week,
            // 'chance' => $chance,
            // 'prize' => $prize
        ];
        
        return view("tng.congratulations", $params);
    }
/*
    private function getPrize($is_spam) {
        $winPrizeId = 0;
        $prizes = Prize::where('rate_min', '>=', 0)->where('rate_max', '>=', 0)->where('is_prize', '>=', '1')->where('quantity', '>', '0')->get();
        if($is_spam) $prizes = Prize::where('id', 5)->where('quantity', '>', '0')->get();

        if(sizeof($prizes) != 0) {
            $minRate = Prize::where('rate_min', '>=', 0)->where('quantity', '>', '0')->orderBy('rate_min', 'asc')->first();
            $maxRate = Prize::where('rate_max', '>=', 0)->where('quantity', '>', '0')->orderBy('rate_max', 'desc')->first();

            if(!$minRate){
                // what?! no more tng, change rate: RM1 id = 4, RM0.50 id = 5
                $minRate = Prize::where('quantity', '>', '0')->where('id', 5)->first();
                $maxRate = Prize::where('quantity', '>', '0')->where('id', 5)->first();
            };

            if($is_spam){
                // this is spam, change rate: RM1 id = 4, RM0.50 id = 5
                $minRate = Prize::where('quantity', '>', '0')->where('id', 5)->first();
                $maxRate = Prize::where('quantity', '>', '0')->where('id', 5)->first();
            };

            $currentRate = 0;

            do {
                $currentRate = mt_rand($minRate->rate_min, $maxRate->rate_max);
                
                foreach ($prizes as $prize) { 
                    if($currentRate >= $prize->rate_min && $currentRate <= $prize->rate_max) {
                        $winPrize = $prize;
                        $winPrizeId = $prize->id;
                        if($prize->id != 10){
                            // exclude please try again from deduct quantity
                            $prize->quantity = $prize->quantity - 1;
                            $prize->save();
                        };
                        break;
                    }
                }
/*
                if($winPrizeId > 0 && $winPrizeId != 10 && $diff == 2){
                    // oh, you win? spin come from the ball side, do it again
                    $winPrizeId = 0;
                    $diff = 1;
                };*//*
            } while($winPrizeId == 0);
        } else {
            // no more prizes, please try again
            $winPrizeId = 10;
        };

        return $winPrizeId;
    }
*/
    public function prizeData() {
        /*
        $data = [
            'prizeData' => Prize::all()
        ];
        */

        $data = [
            ['id' => 1, 'name' => 'Q1'],
            ['id' => 2, 'name' => 'Q2'],
            ['id' => 3, 'name' => 'Q3'],
            ['id' => 4, 'name' => 'Q4'],
            ['id' => 5, 'name' => 'Q5'],
            ['id' => 6, 'name' => 'Q6'],
            ['id' => 7, 'name' => 'Q7'],
            ['id' => 8, 'name' => 'Q8'],
            ['id' => 9, 'name' => 'Q9']
        ];

        // return $data;
        return response()->json(['prizeData' => $data], 200);
    }
/*
    public function spin_api(Request $request){ 
        // $request->session()->put('spin', false);
        $user = Auth::guard('web')->user();
        if (!$user) {
            return redirect()->route('home.login');
        };
        $chance = $user->chance;

        $prize = 0;
        $tng_id = 0;
        $tng_pin = 'XXXXX-XXXXX-XXXXX';
        if($chance >= 1){
            // deduct user chance
            $chance = $chance-1;
            $user->chance = $chance;
            $user->save();

            // pump in prize id
            $prize = $this->getPrize();
            // $request->session()->put('prize_id', $prize);
            $value = 0;
            if($prize == 4){
                $value = 1;
            } else if($prize == 3){
                $value = 3;
            } else if($prize == 2){
                $value = 10;
            } else if($prize == 1){
                $value = 15;
            };

            // get allowed tng list
            $tngList = Tng::where('value', $value)->where('status', 1)->get();
            // tng randomization
            $max = count($tngList);
            if($max > 0){
                $thisIndex = mt_rand(1, $max);
                $tng = $tngList[$thisIndex-1];

                // mark tng as used
                $tngUsed = Tng::where('id', $tng->id)->update(['status' => 0]);
                $tng_id = $tng->id;
                $tng_pin = $tng->pin;
            };

            // create spin record
            $param = [
                'user_id' => $user->id,
                'prize_id' => $prize,
                'tng_id' => $tng_id,
                'trackip' => $_SERVER['REMOTE_ADDR'],
                'status' => 1
            ];
            $spin_record = Spin::create($param);
        };

        // return $prize;
        return response()->json(['prize' => $prize, 'pin' => $tng_pin, 'chance' => $chance], 200);
    }
*/
    private function notify_email($prize){
        // check prize left
        $thisPrize = Prize::where('id', $prize)->first();
        if($thisPrize->quantity == 100 || $thisPrize->quantity == 50){
            // check if email is sent before
            $email_log = EmailLog::where('prize_id', $prize)->where('tng_amount', $thisPrize->quantity)->first();

            if($email_log) {
                // email is sent before, do nothing
            } else {
                // send email
                $tng_amount = $thisPrize->quantity;
                $tng_value = intval(substr($thisPrize->name, 3));
                
                // create email log
                $param = [
                    'prize_id' => $prize,
                    'tng_amount' => $tng_amount,
                    'tng_value' => $tng_value,
                    'result' => 'pending'
                ];
                $temp = EmailLog::create($param);
            };
        };
    }
}