<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\MatchEntry;
use App\Model\BallEntry;
use GuzzleHttp\Client;
use Carbon\Carbon;

class LiveMatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'match:live';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'API to update live score of the matches';

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
        $rawLiveMatches = $this->getLiveMatches();

        foreach ($rawLiveMatches as $matches) {
            MatchEntry::where('match_id', $matches['match_id'])
                ->update($matches);
        }

        $this->info('match:live completed.');
    }

    public function getLiveMatches()
    {
        $matchScore = [];

        $api_url = 'https://api.statorium.com/api/v1/matches/live/?season_id=121&apikey=127d6a2d6457c957d257caaa0ffcb412';
        $client = new Client();

        try {
            $res = $client->get($api_url)->getBody()->getContents();
            $matches = json_decode($res)->matches;

            foreach ($matches as $match) {
                $matchScore[] = $this->getMatchScore($match->matchID);
            }
        } catch (\Throwable $th) {
            throw $th;
        }

        return $matchScore;
    }

    public function getMatchScore($match_id)
    {
        $api_url = 'https://api.statorium.com/api/v1';
        $api_key = '127d6a2d6457c957d257caaa0ffcb412';
        $season_id = 121;
        $method = 'matches/' . $match_id . '/live/';
        $url = $api_url . '/' . $method . '?season_id=' . $season_id . '&apikey=' . $api_key;

        $payload = [];
        $client = new Client();

        try {
            $res = $client->get($url)->getBody()->getContents();
            $match = json_decode($res)->match;
            $stages = json_decode($res)->stages;

            $payload['match_id'] = $match_id;
            $payload['match_status'] = $match->matchStatus->statusID;
            $payload['team_a_score'] = $match->homeParticipant->score;
            $payload['team_b_score'] = $match->awayParticipant->score;
            $payload['winning_team'] = $this->getMatchWinner($match_id, $match->homeParticipant, $match->awayParticipant, $stages);

            return $payload;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getMatchWinner($id, $home, $away, $stages)
    {
        $winner = 0;
        if ($home->score > $away->score) { // if home winning
            $winner = $home->participantID;
        } elseif ($away->score > $home->score) { // if away winning
            $winner = $away->participantID;
        } elseif ($home->score === $away->score) { // if tie
            $winner = $this->getPenaltyMatch($id, $home, $away, $stages); // decide by penalty match
        } else {
            $winner = 0;
        }
        return $winner;
    }

    public function getPenaltyMatch($id, $home, $away, $stages)
    {
        $winnerPM = 0;
        foreach ($stages as $stage) {
            if (strpos(strtolower($stage->stageName), 'penalty') !== false) {
                $home_score = $stage->home_score;
                $away_score = $stage->away_score;
                if ($home_score > $away_score) {
                    $winnerPM = $home->participantID;
                } elseif ($away_score > $home_score) {
                    $winnerPM = $away->participantID;
                } elseif ($home_score === $away_score) {
                    $winnerPM = 0;
                } else {
                    $winnerPM = 0;
                }
            }
        }
        return $winnerPM;
    }
}
