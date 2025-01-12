<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\User;
use App\Model\SpinAcquireRecord;
use App\Model\WeeklyRankRecord;

class WeeklySpin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weekly:spin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Distribute spin according to user\'s rank';

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
        // get latest week 
        $latestRecord = WeeklyRankRecord::orderBy('created_at', 'DESC')->first();
        $week = $latestRecord->week;

        $thisWeekRecord = WeeklyRankRecord::where('week', $week)->get();
        foreach ($thisWeekRecord as $record) {
            $spin = 0;
            $rankRef = $record->rank;
            if($rankRef <= 100){
                $spin = 3;
            } else if($rankRef > 100 && $rankRef <= 300){
                $spin = 2;
            } else if($rankRef > 300 && $rankRef <= 1000){
                $spin = 1;
            };

            if($record->status == 1 && $spin > 0){
                // mark as spent to ensure distribute once only
                $record->status = 2;
                $record->save();

                // distribute spin
                // spin distribution to further implement as seperate command
                $user = User::where('id', $record->user_id)->first();
                $user->chance = $user->chance+$spin;
                $user->save();

                // record distribution
                $record = SpinAcquireRecord::create([
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'spin' => $spin,
                    'reference' => 'Weekly Rank: '.$week
                ]);
            };
        };

        $this->info('weekly spin complete.');
    }
}