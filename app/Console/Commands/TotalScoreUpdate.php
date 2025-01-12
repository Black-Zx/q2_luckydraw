<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\BallEntry;
use App\Model\ScoreSummary;
use Illuminate\Support\Facades\DB;

class TotalScoreUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'total_score:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Task to calculate and sum up all user scores';

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
        // Since it's running in async. We execute the function by sequence.
        $this->calculateMatchScore(); // Calculate match score

        $this->calculateTotalScore(); // Calculate total score

        $this->info('total_score:update completed.');
    }

    public function calculateMatchScore()
    {
        $match_scores = BallEntry::orderBy('user_score', 'desc')
            ->where('user_score', '!=', 0)
            ->groupBy('user_id')
            ->get([
                'user_id',
                DB::raw('sum(user_score) as match_score')
            ]);

        if ($match_scores) {
            foreach ($match_scores as $score) {
                ScoreSummary::where('user_id', $score['user_id'])
                    ->update(['match_score' => $score['match_score']]);
            }
        }
    }

    public function calculateTotalScore()
    {
        $total_scores = ScoreSummary::where('match_score', '!=', 0)
            ->orWhere('game_score', '!=', 0)
            ->get([
                'user_id',
                'match_score',
                'game_score',
                // DB::raw('match_score + game_score as total_score')
            ]);

        if ($total_scores) {
            foreach ($total_scores as $score) {
                $total = $score['match_score'] + $score['game_score'];
                ScoreSummary::where('user_id', $score['user_id'])
                    ->update([
                        'match_score' => $score['match_score'],
                        'game_score' => $score['game_score'],
                        'total_score' => $total
                    ]);
            }
        }
    }
}
