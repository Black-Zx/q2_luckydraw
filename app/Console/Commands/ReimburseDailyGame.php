<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\Spin;
use App\Model\CheckIn;
use App\Model\Prize;
use App\Model\Tng;

class ReimburseDailyGame extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reimburse:1';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'redistribute tng spin - Daily Game';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // DAILY GAME
        // *spin_record status = 1 and tng_id = 0
        $daily_game_list = Spin::join('users', 'spin_record.user_id', '=', 'users.id')
                    ->where('users.status', 1)
                    ->where('spin_record.created_at', '>=', '2024-01-19 00:00:00')
                    ->where('spin_record.created_at', '<=', '2024-04-01 23:59:59')
                    ->where('spin_record.status', 1)
                    ->where('spin_record.tng_id', 0)
                    // ->where('users.id', '>=', 4420)
                    ->groupBy('spin_record.user_id')
                    // ->get()->toArray();
                    ->pluck('spin_record.user_id')->toArray();
        // return '<pre>'.print_r($daily_game_list, true).'</pre>';
        $reimburseList = $this->reimburse_record($daily_game_list);
        // $result .= '<pre>Daily Game<br>user entitled:<br>'.print_r($daily_game_list, true).'<br>reimburse:<br>'.print_r($reimburseList, true).'</pre>';

        $this->info('ReimburseDailyGame complete.');
    }

    private function reimburse_record($userList){
        $result = [];

        // Daily game
        for($i=0; $i<count($userList); $i++){
            // check if already re-imburse
            $spin_record = Spin::where('user_id', $userList[$i])
                            ->where('created_at', '>=', '2024-01-19 00:00:00')
                            ->where('created_at', '<=', '2024-04-01 23:59:59')
                            ->where('status', 1)
                            ->where('tng_id', 0)
                            ->get()->toArray();
            
            // for($j=0; $j<count($scopeList); $j++){
            foreach ($spin_record as $this_spin){
                $this_scope = date("Ymd", strtotime($this_spin['created_at']))."-rolex-re";
                $exist = Spin::where('user_id', $userList[$i])
                            ->where('scope', $this_scope)
                            ->where('status', 1)
                            ->get()->count();

                if($exist > 0){
                    // do nothing
                    $this->info($userList[$i].', scope: '.$this_scope.', already reimburse, do nothing.');
                } else {
                    // reimburse
                    $prize = 0;
                    $value = 0;

                    // RM0.50~5
                    // $prizes = Prize::whereIn('id', [3,4,5,6])->where('is_prize', '>=', '1')->where('quantity', '>', '0')->get();
                    // force all RM0.50
                    $prizes = Prize::where('id', 5)->first();
                    /*
                    if(sizeof($prizes) != 0) {
                        $minRate = Prize::whereIn('id', [3,4,5,6])->where('quantity', '>', '0')->orderBy('rate_min', 'asc')->first();
                        $maxRate = Prize::whereIn('id', [3,4,5,6])->where('quantity', '>', '0')->orderBy('rate_max', 'desc')->first();

                        $currentRate = 0;

                        do {
                            $currentRate = mt_rand($minRate->rate_min, $maxRate->rate_max);
                            
                            foreach ($prizes as $thisPrize) { 
                                if($currentRate >= $thisPrize->rate_min && $currentRate <= $thisPrize->rate_max) {
                                    $winPrize = $thisPrize;
                                    $prize = $thisPrize->id;
                                    if($thisPrize->id != 10){
                                        // exclude please try again from deduct quantity
                                        $thisPrize->quantity = $thisPrize->quantity - 1;
                                        $thisPrize->save();
                                    };
                                    break;
                                }
                            }
                        } while($prize == 0);
                    };

                    if($prize == 4){
                        $value = 1;
                    } else if($prize == 3){
                        $value = 3;
                    } else if($prize == 5){
                        $value = 0.5;
                    } else if($prize == 6){
                        $value = 5;
                    };

                    // get allowed tng list
                    $tngList = Tng::where('value', $value)->where('type', '>=', 2)->where('status', 1)->limit(1000)->get();
                    // tng randomization
                    $max = count($tngList);
                    if($max > 0){
                        $thisIndex = mt_rand(1, $max);
                        $tng = $tngList[$thisIndex-1];

                        // mark tng as used
                        $tngUsed = Tng::where('id', $tng->id)->update(['status' => 0]);
                        $tng_id = $tng->id;

                        // create spin record
                        $param = [
                            'user_id' => $userList[$i],
                            'prize_id' => $prize,
                            'tng_id' => $tng_id,
                            'scope' => $this_scope,
                            'trackip' => '127.0.0.1', //$_SERVER['REMOTE_ADDR'],
                            'status' => 1
                        ];
                        $spin_record = Spin::create($param);

                        if($spin_record) array_push($result, $userList[$i].', '.$this_scope);

                        $this->info('User: '.$userList[$i].', scope: '.$this_scope);
                    };
                    */

                    if($prizes->quantity > 0){
                        // deduct prize quantity
                        $prizes->quantity = $prizes->quantity - 1;
                        $prizes->save();
                        
                        // get allowed tng list
                        $tngList = Tng::where('value', 0.5)->where('type', '>=', 2)->where('status', 1)->limit(1000)->get();
                        // tng randomization
                        $max = count($tngList);
                        if($max > 0){
                            $thisIndex = mt_rand(1, $max);
                            $tng = $tngList[$thisIndex-1];

                            // mark tng as used
                            $tngUsed = Tng::where('id', $tng->id)->update(['status' => 0]);
                            $tng_id = $tng->id;

                            // create spin record
                            $param = [
                                'user_id' => $userList[$i],
                                'prize_id' => $prize,
                                'tng_id' => $tng_id,
                                'scope' => $this_scope,
                                'trackip' => '127.0.0.1', //$_SERVER['REMOTE_ADDR'],
                                'status' => 1
                            ];
                            $spin_record = Spin::create($param);

                            if($spin_record) array_push($result, $userList[$i].', '.$this_scope);

                            $this->info('User: '.$userList[$i].', scope: '.$this_scope);
                        };
                    };
                };
            };
        };

        return $result;
    }
}