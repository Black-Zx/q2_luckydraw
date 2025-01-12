<?php
namespace App\Http\Controllers;

use App\Model\User;
use App\Model\Trackings;
use DateTime;
use DateInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class Helper extends Controller{
	
	public function __construct() {
		// $this->middleware(['auth:web']);
	}

    public function getWeek(){
        // no longer accurate due to campaign time
        /*
        $startDate = strtotime('2023-02-06');
        $today = strtotime("now");
        // $lastweekToday = strtotime("-7 day", $today);
        // $lastSunday = strtotime('sunday this week', $lastweekToday);
        $diff = $today - $startDate;
        $week = floor($diff/(60*60*24*7))+1;
        // temp hack
        // $week = 2;
        */
        $dateList = ['2024-04-01', '2024-04-15', '2024-04-29', '2024-05-13', '2024-05-27'];
        $nextID = 0;
        $now = new DateTime();
        // $startDate = strtotime("2023-05-01");
        for($i=0; $i<count($dateList); $i++){
            $thisDate = strtotime($dateList[$i]);
            if($now->getTimestamp() >= $thisDate){
                $nextID = $i;
            };
        };
        $week = $nextID + 1;
        // if($week > count($dateList)-1) $week = count($dateList)-1;

        if($this->isStaging()) {
            $now->add(new DateInterval('P7D'));
            $available_date = (count($dateList) > $week) ? strtotime($dateList[$week]) : strtotime($dateList[$week-1]); // get next week date if it is available
            if($now->getTimestamp() >= $available_date){
                // temp disable week increment
                $week = $week + 1;
            };

            // force week 3 for debug purpose
            // if($week < 3) $week = 3;
        }
        if($week > count($dateList)) $week = count($dateList);

        return $week;
    }

    public function campaign_end(){
        $now = strtotime("now");
        $this_end = strtotime('2024-07-30 20:00:00');
        $end = ($now > $this_end) ? true : false;

        return $end;
    }

    public function isStaging(){
        $staging = false;
        $BaseUrl = url('/');
        if($BaseUrl == 'http://localhost:8000' || $BaseUrl == 'https://stgbat-one.myecdc.com'){
            $staging = true;
        };

        return $staging;
    }

    public function uuid_checker(){
        $status = false;
        if(Auth::check()){
            $uuid = Cache::get('uuid_'.Auth::user()->id);
            $today = date("Y-m-d H:i:s");
            $today_start = date("Y-m-d H:i:s", strtotime("-1 days", strtotime($today)));

            $total = Trackings::where('uuid', $uuid)
                        ->where('created_at', '>=', $today_start)->where('created_at', '<=', $today)
                        ->groupBy('user_id')->pluck('user_id')->count();

            // if more than 5 logins within 24hrs, return true
            $status = ($total > 5) ? true : false;
        };

        return $status;
    }
}