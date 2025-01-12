<?php

// Localization.php

namespace App\Http\Middleware;

use Closure;
use App;
use App\Model\Trackings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TrackerLog
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (Auth::guard('web')->check()) {
            // get current session ip
            $ip = $_SERVER['REMOTE_ADDR']; //$request->ip(); // DB::table('sessions')->where('id', session()->getId())->first()->ip_address;

            // get all session under same ip
            $session_group = DB::table('sessions')->where('ip_address', $ip)
                                ->groupBy('id')->pluck('id')->toArray();
            // get all ip from related session above
            $session_ip = DB::table('sessions')->whereIn('id', $session_group)
                                ->groupBy('ip_address')->pluck('ip_address')->toArray();
            array_push($session_ip, $_SERVER['REMOTE_ADDR']);
            // get all user who is under same ip
            $user_group = DB::table('sessions')->whereIn('ip_address', $session_ip)
                            ->groupBy('user_id')->pluck('user_id')->toArray();
            $ip_group = DB::table('sessions')->whereIn('user_id', $user_group)
                            ->groupBy('ip_address')->pluck('ip_address')->toArray();
            array_push($ip_group, $_SERVER['REMOTE_ADDR']);
            // $final_group = DB::table('sessions')->whereIn('ip_address', $ip_group)
                            // ->groupBy('user_id')->pluck('user_id')->toArray();

            // get user who is under same ip from trackings
            $today = date("Y-m-d H:i:s");
            $today_start = date("Y-m-d H:i:s", strtotime("-1 days", strtotime($today))); //$today." 00:00:00";
            $today_end = $today; //." 23:59:59";
            // get recorded ip along activities
            $track_ip = Trackings::whereIn('user_id', $user_group)
                                ->where('created_at', '>=', $today_start)->where('created_at', '<=', $today_end)
                                ->groupBy('ip_address')->pluck('ip_address')->toArray();
            $ip_group = array_merge($ip_group, $track_ip);
            $tracking_users = Trackings::whereIn('ip_address', $ip_group)
                                ->where('created_at', '>=', $today_start)->where('created_at', '<=', $today_end)
                                ->groupBy('user_id')->pluck('user_id')->toArray();
            array_push($tracking_users, Auth::guard('web')->user()->id);

            $uuid = null;
            // check if I have my own uuid
            $uuid = Cache::get('uuid_'.Auth::guard('web')->user()->id);
            if($uuid){
                // already have uuid, update cache time
                $cache = Cache::put('uuid_'.Auth::guard('web')->user()->id, $uuid, 86400);
            } else {
                // dont have own uuid, get any existing uuid from existing session of same ip
                for($i=0; $i<count($tracking_users); $i++){
                    // get cache
                    $uuid = Cache::get('uuid_'.$tracking_users[$i]);
                    if($uuid){
                        // cache is found, end loop
                        break;
                    };
                };

                // $user = Auth::guard('web')->user();
                if($uuid){
                    // apply the same uuid to this user
                    $cache = Cache::put('uuid_'.Auth::guard('web')->user()->id, $uuid, 86400);
                } else {
                    // assign new uuid
                    $uuid = Str::uuid();
                    $cache = Cache::put('uuid_'.Auth::guard('web')->user()->id, $uuid, 86400);
                };
            };

            // write into DB first
            $intent = $request->path(); //$request->path() === 'puzzleComplete' ? 'dunhill_games_week_' . $games_week : $request->path();
            $payload = array(
                'user_id' => Auth::guard('web')->user()->id,
                'intent' => $intent,
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'uuid' => $uuid
            );
            Trackings::create($payload);

            // final checking: check if any different uuid is recorded in trackings
            $difference = [];
            $diff_id = [];
            for($i=0; $i<count($tracking_users); $i++){
                // get cache
                $temp_uuid = Cache::get('uuid_'.$tracking_users[$i]);
                if($temp_uuid != $uuid){
                    // different uuid detected!!
                    array_push($difference, $temp_uuid);
                    array_push($diff_id, $tracking_users[$i]);
                };
            };
            if(count($difference) > 0){
                // consolidate different uuid as one
                $update_trackings = Trackings::whereIn('uuid', $difference)
                                        ->where('created_at', '>=', $today_start)->where('created_at', '<=', $today_end)
                                        ->update(['uuid' => $uuid]);
                // update cache as well
                for($i=0; $i<count($diff_id); $i++){
                    $update_cache = Cache::put('uuid_'.$diff_id[$i], $uuid, 86400);
                };
            };
        }

        return $response;
    }
}
