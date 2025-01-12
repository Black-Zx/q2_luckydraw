<?php

// Localization.php

namespace App\Http\Middleware;

use Closure;
use App;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Helper;
use App\Model\SurveyEntry;

class SurveyCheck
{
    public $Helper;

    public function __construct(Helper $Helper)
    {
        $this->Helper = $Helper;
    }
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

        if(Auth::check()){
            if(!request()->is('survey*')){
                $end = $this->Helper->campaign_end();
                if(!$end){
                    // check if user completed survey
                    $user = Auth::guard('web')->user();
                    $complete = SurveyEntry::where('user_id', $user['id'])->where('status', 1)->first();
                    if(!$complete){
                        return redirect()->route('survey.survey');
                    };
                };                
            };
	    };

        return $response;
    }
}