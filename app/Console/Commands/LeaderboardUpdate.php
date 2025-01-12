<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\BallEntry;
use App\Model\Quiz;
use App\Model\User;
use App\Model\ScoreSummary;
use App\Model\Leaderboard;

class LeaderboardUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leaderboard:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create/update list of consolidated leaderboard';

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

        // $temp = PuzzleEntry::select([DB::raw('MAX(score) AS max_score'), 'user_id AS max_user_id'])
        //     ->leftJoin('users', 'puzzle.user_id', '=', 'users.id')
        //     // ->where('users.status', '1')
        //     ->groupBy('max_user_id');
        // $week3 = PuzzleEntry::joinSub($temp, 'Temp', function ($join) {
        //     $join->on('score', '=', 'max_score')
        //         ->on('user_id', '=', 'max_user_id');
        // })
        //     ->join('users', 'puzzle.user_id', '=', 'users.id')
        //     ->select(['users.name', 'users.preferred_name', 'puzzle.user_id', 'puzzle.score', 'puzzle.updated_at'])
        //     ->groupBy(['user_id'])
        //     ->orderBy('score', 'DESC')
        //     ->orderBy('updated_at', 'ASC')
        //     ->take(10)
        //     ->get();

        $scores = ScoreSummary::orderBy('total_score', 'DESC')
            ->orderBy('updated_at', 'ASC')
            ->where('total_score', '>', 0)
            // ->take(10)
            ->get();

        // truncate leaderboard
        Leaderboard::truncate();

        // pump in data
        $rank = 1;
        $previous = null;
        foreach ($scores as $score) {
            if ($previous && $previous->total_score != $score->total_score) {
                $rank++;
            }

            $score->rank = $rank;

            unset($score->id);
            unset($score->created_at);
            unset($score->updated_at);
            $previous = $score;

            Leaderboard::create([
                'user_id' => $score->user_id,
                'user_name' => $score->user_name,
                'match_score' => $score->match_score,
                'game_score' => $score->game_score,
                'total_score' => $score->total_score,
                'rank' => (int) $rank
            ]);
        }

        // return result to terminal
        $this->info('leaderboard:update completed.');
    }
}
