<?php

// Localization.php

namespace App\Http\Middleware;

use Closure;
use App;
use App\Model\Trackings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class Staging
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
        if (session()->has('is_staging')) {
            session()->get('is_staging');
        } else {
            // locale session not exist, initialize as english
            session()->put('is_staging', $this->isStaging());
        };
        // force as staging session
        session()->put('is_staging', true);

        // ip check
        if(Auth::check()){
            $today = date("Y-m-d H:i:s");
            $today_start = date("Y-m-d H:i:s", strtotime("-1 days", strtotime($today)));
            $track_ip = Trackings::where('user_id', Auth::user()->id)
                            ->where('created_at', '>=', $today_start)->where('created_at', '<=', $today)
                            ->groupBy('ip_address')->pluck('ip_address')->toArray();

            $this_ip = $_SERVER['REMOTE_ADDR'];
            if(in_array($this_ip, $track_ip)) {
                // do nothing
            } else {
                // record the new ip
                $uuid = Cache::get('uuid_'.Auth::user()->id);
                $params = array(
                    'user_id' => Auth::user()->id,
                    'intent' => 'log-ip',
                    'ip_address' => $this_ip,
                    'uuid' => $uuid
                );
                Trackings::create($params);
            };
        };

        return $next($request);
    }

    private function isStaging(){
        $staging = false;
        $BaseUrl = url('/');
        if($BaseUrl == 'http://localhost:8000' || $BaseUrl == 'https://stgbat-one.myecdc.com'){
            $staging = true;
        };

        return $staging;
    }
}