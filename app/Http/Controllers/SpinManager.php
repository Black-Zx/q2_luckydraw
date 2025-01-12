<?php
namespace App\Http\Controllers;

use App\Model\User;
use App\Model\Prize;
use App\Model\Prize2;
use App\Model\Spin;
use App\Model\Trackings;
use DateTime;
use DateInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Log;

class SpinManager extends Controller{
    private $campaign_start = '2024-06-25 00:00:00';
    public $Helper;
	
	public function __construct(Helper $Helper) {
		$this->Helper = $Helper;
	}

    public function SpinExist($user_id){
        $exist = -1;
        $spin_entry = Spin::where('user_id', $user_id)->where('prize_id', -1)->where('created_at', '>=', $this->campaign_start)->first();

        if($spin_entry){
            $exist = $spin_entry['id'];
        };

        return $exist;
    }

    public function SpinPlaceholder($user_id){
        // check if place holder already created
        $spin_entry = Spin::where('user_id', $user_id)->where('prize_id', -1)->where('created_at', '>=', $this->campaign_start)->first();
        if(!$spin_entry){
            // create spin record
            $param = [
                'user_id' => $user_id,
                'prize_id' => -1,
                'tng_id' => -1,
                'scope' => 'lucky-draw',
                'trackip' => $_SERVER['REMOTE_ADDR'],
                'status' => 1
            ];
            $spin_entry = Spin::create($param);
        };

        return $spin_entry['id'];
    }

    public function updateSpinRecord($spin_id, $user_id){
        // Log::info('updateSpinRecord');

        $result = false;
        $user = Auth::guard('web')->user();
        if($user->id != $user_id) return $result;

        // Log::info('user check complete');
        $today = date("Y-m-d");
        $today_start = $today." 00:00:00";
        $today_end = $today." 23:59:59";

        $thisChance = $user->chance;
        $spin_entry = Spin::where('id', $spin_id)->where('user_id', $user_id)->where('created_at', '>=', $this->campaign_start)->first();

        if($thisChance > 0 && $spin_entry){
            // deduct chance
            $user->chance = $thisChance-1;
            $user->save();

            if($spin_entry->prize_id != -1){
                // hacker
                // do nothing
                $result = true;
            } else {
                // check if total spin more than active users
                // todo
                // $allowed = $this->AllowedTaskExceedMax('lucky-draw');
                $allowed = true;
                if($allowed){
                    $is_spam = false;
                    $this_ip = $_SERVER['REMOTE_ADDR'];
                    $total_spin = Spin::where('trackip', $this_ip)
                                    ->where('created_at', '>=', $today_start)->where('created_at', '<=', $today_end)->get()->count();
                    if($total_spin >= 999) $is_spam = true;
                    
                    if(!$is_spam){
                        if ($user->batch == 1) {
                            $prize = $this->getPrize($is_spam);
                        } else {
                            $prize = $this->getPrize2($is_spam);
                        }
                        
                    } else {
                        // possible spammer, get prize tng pin value from RM0.50
                        // $prize = 999;
                        
                        if ($user->batch == 1) {
                            $prizetbl = Prize::orderBy('id', 'desc')->first();
                        } else{
                            $prizetbl = Prize2::orderBy('id', 'desc')->first();
                        }
                        $prize = $prizetbl->id;
                    };

                    if(isset($prize)){
                        // update spin record
                        $now = new DateTime();
                        $update = Spin::where('id', $spin_id)->where('user_id', $user_id)
                                    ->update(['prize_id' => $prize, 'updated_at' => $now]);
                        $result = true;
                    };
                } else {
                    // update spin record
                    $now = new DateTime();
                    $update = Spin::where('id', $spin_id)->where('user_id', $user_id)
                                ->update(['prize_id' => 999, 'updated_at' => $now]);
                    $result = true;
                };
            };
        };

        return $result;
    }

    public function AllowedTaskExceedMax($scope){
        $status = 1;

        $total_spin = Spin::where('prize_id', '>', 0)->where('scope', $scope)->where('status', $status)->get()->count();
        $today = strtotime("now");
        $this_start = strtotime('2024-06-25');
        $diff = $today - $this_start;
        $week = intval(floor($diff/(60*60*24*7)))+1;
        $dateList = [];
        $total_active_users = 0;
        $days = 7;
        $i = (intval(substr($scope, -1)) - 1)*2; // 2 weeks 1 task
        if($this->Helper->isStaging()){
            $i--;
        };
        for($i; $i<$week; $i++){
            $monday = date("Y-m-d", strtotime((7*$i)." day", $this_start))." 00:00:00";
            $sunday = date("Y-m-d", strtotime((7*$i + 6)." day", $this_start))." 23:59:59";
            array_push($dateList, [$monday, $sunday]);

            $total_active_users += Trackings::where('created_at', '>=', $monday)->where('created_at', '<=', $sunday)->groupBy('user_id')->get()->count()*$days;
        };

        // return ['week' => $week, 'total_active_users' => $total_active_users, 'dateList' => $dateList, 'total_spin' => $total_spin];
        
        $allowed = false;
        if($total_spin < $total_active_users){
            $allowed = true;
        };

        return $allowed;
    }

    public function getSpinResult($spin_id, $user_id){
        $thisSpin = Spin::where('id', $spin_id)->where('user_id', $user_id)->first();
        return $thisSpin->prize_id;
    }

    public function getImgResult($spin_id, $user_id){
        $thisSpin = Spin::where('id', $spin_id)->where('user_id', $user_id)->first();
        // return $thisSpin->prize_id;
        $thisImg = Prize::where('id', $thisSpin->prize_id)->first();
        return $thisImg->image_url;
    }

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
            } while($winPrizeId == 0);
        } else {
            // no more prizes, please try again
            $winPrizeId = 10;
        };

        return $winPrizeId;
    }

    private function getPrize2($is_spam) {
        $winPrizeId = 0;
        $prizes = Prize2::where('rate_min', '>=', 0)->where('rate_max', '>=', 0)->where('is_prize', '>=', '1')->where('quantity', '>', '0')->get();
        if($is_spam) $prizes = Prize2::where('id', 5)->where('quantity', '>', '0')->get();

        if(sizeof($prizes) != 0) {
            $minRate = Prize2::where('rate_min', '>=', 0)->where('quantity', '>', '0')->orderBy('rate_min', 'asc')->first();
            $maxRate = Prize2::where('rate_max', '>=', 0)->where('quantity', '>', '0')->orderBy('rate_max', 'desc')->first();

            if(!$minRate){
                // what?! no more tng, change rate: RM1 id = 4, RM0.50 id = 5
                $minRate = Prize2::where('quantity', '>', '0')->where('id', 5)->first();
                $maxRate = Prize2::where('quantity', '>', '0')->where('id', 5)->first();
            };

            if($is_spam){
                // this is spam, change rate: RM1 id = 4, RM0.50 id = 5
                $minRate = Prize2::where('quantity', '>', '0')->where('id', 5)->first();
                $maxRate = Prize2::where('quantity', '>', '0')->where('id', 5)->first();
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
            } while($winPrizeId == 0);
        } else {
            // no more prizes, please try again
            $winPrizeId = 10;
        };

        return $winPrizeId;
    }
}