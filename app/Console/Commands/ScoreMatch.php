<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\MatchEntry;
use App\Model\BallEntry;

class ScoreMatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'match:score';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Task to update user point based on match score';

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
        $matches = MatchEntry::where('match_status', '=', 1)->get();
        if ($matches) {
            foreach ($matches as $match) {
                $entries = BallEntry::where([
                    ['user_score', '=', NULL],
                    ['match_id', '=', $match['match_id']]
                ])->get();

                foreach ($entries as $ball_entry) {
                    $this->getRealScore($ball_entry, $match);
                }
            }
        }

        $this->info('match:score completed.');
    }

    public function getRealScore($ball_entry, $match)
    {
        // $user_match_id = $ball_entry->match_id;

        // $matches = MatchEntry::where([
        //     ['match_id', '=', $user_match_id],
        //     ['match_status', '=', 1]
        // ])
        //     ->get();
        // foreach ($matches as $match) {
        $scores = $this->getCalculatedScore($ball_entry, $match);

        // echo $match->match_id . ' - ' . $scores['user_id'] . ' - ' . $scores['user_score'] . PHP_EOL;
        BallEntry::where('id', $scores['id'])
            ->update(['user_score' => $scores['user_score']]);
        // }
    }

    public function getCalculatedScore($entries, $matches)
    {
        $match_score_a = $matches->team_a_score;
        $match_score_b = $matches->team_b_score;
        $match_winning_team = (string) $matches->winning_team;
        $user_score_a = $entries['score_team_a'];
        $user_score_b = $entries['score_team_b'];
        $user_winning_team = (string) $entries['winning_team'];
        $user_score = 0;

        if ($user_winning_team) {

            if ($user_winning_team == '0' || $user_winning_team != $match_winning_team) {
                $user_score = 0;
            } elseif ($user_winning_team == $match_winning_team && $match_score_a == $user_score_a && $match_score_b == $user_score_b) {
                $user_score = 5;
            } elseif ($user_winning_team != $match_winning_team && $match_score_a == $user_score_a && $match_score_b == $user_score_b) {
                $user_score = 0;
            } elseif ($user_winning_team == $match_winning_team) {
                $user_score = 2;
            }
        } else {

            if ($match_score_a == $user_score_a && $match_score_b == $user_score_b) {
                $user_score = 5;
            }
        }

        $entries['user_score'] = $user_score;
        return $entries;
    }
}
