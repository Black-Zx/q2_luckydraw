<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\User;
use App\Model\WeeklyRankRecord;

class WeeklyRank extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weekly:rank';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update rank and distribute spin per week';

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
        $csvFile = fopen(base_path("database/data/dummy_rank.csv"), "r");
        $firstline = true;
        while (($data = fgetcsv($csvFile, 2000, ",")) !== FALSE) {
            if (!$firstline) {
                $username = $data['0'];
                $user = User::where('username', $username)->first();
                $spin = 0;
                $rankRef = $data['1'];
                if($rankRef <= 100){
                    $spin = 3;
                } else if($rankRef > 100 && $rankRef <= 300){
                    $spin = 2;
                } else if($rankRef > 300 && $rankRef <= 1000){
                    $spin = 1;
                };
                
                $record = WeeklyRankRecord::create([
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'rank' => $data['1'],
                    'week' => $data['2'],
                    'target' => $data['3'],
                    'target_achieved' => $data['4'],
                    'spin' => $spin,
                ]);
            }
            $firstline = false;
        }
        fclose($csvFile);

        $this->info('weekly rank complete.');
    }
}