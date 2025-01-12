<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\Spin;
use App\Model\CheckIn;
use App\Model\Prize;
use App\Model\Tng;

class ReimburseLogin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reimburse:3';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'redistribute tng spin - Daily checkin';

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
        // DAILY CHECKIN
        //*refer check_in table, cross check with spin_record status = 3 and tng_id = 0
        $user_list = Spin::join('users', 'spin_record.user_id', '=', 'users.id')
                    ->where('users.status', 1)
                    ->where('spin_record.created_at', '>=', '2024-01-19 00:00:00')
                    ->where('spin_record.created_at', '<=', '2024-04-01 23:59:59')
                    ->where('spin_record.status', 3)
                    ->where('spin_record.tng_id', 0)
                    // ->where('users.id', '>=', 4207)
                    ->groupBy('spin_record.user_id')
                    // ->get()->toArray();
                    ->pluck('spin_record.user_id')->toArray();
        // return '<pre>'.print_r($user_list, true).'</pre>';
        $reimburseList = $this->reimburse_record($user_list);
        // $result .= '<pre>Daily Checkin<br>user entitled:<br>'.print_r($user_list, true).'<br>reimburse:<br>'.print_r($reimburseList, true).'</pre>';

        $this->info('ReimburseLogin complete.');
    }

    private function reimburse_record($userList){
        $result = [];

        // daily checkin
        for($i=0; $i<count($userList); $i++){
            $spin_record = Spin::where('user_id', $userList[$i])
                            ->where('created_at', '>=', '2024-01-19 00:00:00')
                            ->where('created_at', '<=', '2024-04-01 23:59:59')
                            ->where('status', 3)
                            ->where('tng_id', 0)
                            ->get()->toArray();
            $max = count($spin_record);
            // var_dump($userList[$i].', '.$max.'<br>');
            // var_dump($spin_record);
            // var_dump($max);
            
            // for($j=0; $j<$max; $j++){
            foreach ($spin_record as $this_spin){
                // var_dump($j);
                // check if already re-imburse
                // if($spin_record[$j]){
                    $this_scope = date("Ymd", strtotime($this_spin['created_at']))."-rolex-re";
                    $exist = Spin::where('user_id', $userList[$i])
                                ->where('scope', $this_scope)
                                ->where('status', 3)
                                ->get()->count();
                    
                    if($exist){
                        // do nothing
                        $this->info($userList[$i].', scope: '.$this_scope.', already reimburse, do nothing.');
                    } else {
                        // reimburse by day
                        $this_date = date("Y-m-d", strtotime($this_spin['created_at']));
                        $checkin_record = CheckIn::where('user_id', $userList[$i])
                                            ->where('date', $this_date)
                                            ->first();

                        $prize = 0;
                        $value = 0;
                        // disable days 7 random, force all RM0.50
                        // if($checkin_record->days == 7){
                        if($checkin_record->days == 999){
                            // RM1~3
                            $prizes = Prize::whereIn('id', [3,4])->where('is_prize', '>=', '1')->where('rate_min', '>', '15000')->where('quantity', '>', '0')->get();
                            if(sizeof($prizes) != 0) {
                                $minRate = Prize::whereIn('id', [3,4])->where('rate_min', '>', '15000')->where('quantity', '>', '0')->orderBy('rate_min', 'asc')->first();
                                $maxRate = Prize::whereIn('id', [3,4])->where('quantity', '>', '0')->orderBy('rate_max', 'desc')->first();

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
                            };
                        } else {
                            // RM0.50
                            $prize = 5;
                            $value = 0.5;
                            $thisPrize = Prize::where('id', 5)->first();
                            if($thisPrize->quantity > 0){
                                // deduct prize quantity
                                $thisPrize->quantity = $thisPrize->quantity - 1;
                                $thisPrize->save();
                            };
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
                                'status' => 3
                            ];
                            $spin_record = Spin::create($param);

                            if($spin_record) array_push($result, $userList[$i].', '.$this_scope);

                            $this->info('User: '.$userList[$i].', scope: '.$this_scope);
                        };
                    };
                // };
            };
        };

        return $result;
    }
}