<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\Spin;
use App\Model\CheckIn;
use App\Model\Prize;
use App\Model\Tng;

class ReimburseWeeklyTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reimburse:2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'redistribute tng spin - Weekly Task';

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
        // WEEKLY TASK
        // *spin_record status = 2 and tng_id = 0
        $list = Spin::join('users', 'spin_record.user_id', '=', 'users.id')
                    ->select(['spin_record.user_id', 'spin_record.tng_id', 'spin_record.scope', 'spin_record.created_at'])
                    ->where('users.status', 1)
                    ->where('spin_record.created_at', '>=', '2024-01-19 00:00:00')
                    ->where('spin_record.created_at', '<=', '2024-04-01 23:59:59')
                    ->where('spin_record.status', 2)
                    ->where('spin_record.tng_id', 0)
                    ->pluck('spin_record.user_id')->toArray();
        // return '<pre>'.print_r($list, true).'</pre>';
        $reimburseList = $this->reimburse_record($list);
        // $result .= '<pre>Weekly Task<br>user entitled:<br>'.print_r($list, true).'<br>reimburse:<br>'.print_r($reimburseList, true).'</pre>';

        $this->info('ReimburseWeeklyTask complete.');
    }

    private function reimburse_record($userList){
        $result = [];

        // weekly task
        for($i=0; $i<count($userList); $i++){
            // check if already re-imburse
            $scopeList = Spin::where('user_id', $userList[$i])
                            ->where('created_at', '>=', '2024-01-19 00:00:00')
                            ->where('created_at', '<=', '2024-04-01 23:59:59')
                            ->where('status', 2)
                            ->where('tng_id', 0)
                            ->pluck('scope')->toArray();
            
            for($j=0; $j<count($scopeList); $j++){
                $this_scope = $scopeList[$j].'-rolex-re';
                $exist = Spin::where('user_id', $userList[$i])
                            ->where('scope', $this_scope)
                            ->where('status', 2)
                            ->get()->count();

                if($exist > 0){
                    // do nothing
                    $this->info($userList[$i].', scope: '.$this_scope.', already reimburse, do nothing.');
                } else {
                    // reimburse
                    $prize = 2;
                    $thisPrize = Prize::where('id', 2)->first();
                    if($thisPrize->quantity > 0){
                        // deduct prize quantity
                        $thisPrize->quantity = $thisPrize->quantity - 1;
                        $thisPrize->save();

                        // get allowed tng list
                        $tngList = Tng::where('value', 10)->where('type', '>=', 2)->where('status', 1)->limit(1000)->get();
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
                                'status' => 2
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