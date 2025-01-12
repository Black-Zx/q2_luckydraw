<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\MatchEntry;
use Carbon\Carbon;
use GuzzleHttp\Client;

class CreateMatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'match:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Call api and create/update list of football matches';

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

        $rawMatches = $this->getMatches();
        $formattedMatches = $this->getFormattedMatches(json_encode($rawMatches));

        foreach (json_decode($formattedMatches) as $matches) {
            $exist = MatchEntry::where('match_id', $matches->match_id)->first();

            if (!$exist) {
                $param = (array)$matches;
                $entry = MatchEntry::create($param);
            } else {
                $update = MatchEntry::where('id', $exist->id)
                    ->update([
                        'match_date' => $matches->match_date,
                        'match_status' => $matches->match_status,
                        // 'team_a_score' => $matches->team_a_score,
                        // 'team_b_score' => $matches->team_b_score,
                        // 'winning_team' => $matches->winning_team
                    ]);
            };
        }

        // return result to terminal
        $this->info('match:create completed.');
    }

    public function getFormattedMatches($raw_matches)
    {

        $payload = [];
        foreach (json_decode($raw_matches) as $items) {
            $stage = $items->stage;
            $playoff = $items->playoff;
            $matches = $items->matches;
            foreach ($matches as $match) {
                $objs = new \stdClass;
                $objs->match_id = $match->matchID;
                $objs->team_a_id = $match->homeParticipant->participantID;
                $objs->team_b_id = $match->awayParticipant->participantID;
                $objs->stage = $match->group && $match->group->groupName ? 'Group Stage ' . $stage : $stage;
                $objs->stage_name = $match->group && $match->group->groupName ? $match->group->groupName : '';

                $match_date_utc = Carbon::createFromFormat('Y-m-d H:i', $match->matchDate . ' ' . $match->matchTime, 'UTC');
                $match_date_utc_new = $match_date_utc->setTimezone('Asia/Singapore');
                $objs->match_date = $match_date_utc_new->format('Y-m-d H:i:s');
                $objs->match_status = !$match->matchStatus->statusID ? '0' : $match->matchStatus->statusID;

                // match_status = 0 Fixture (a match arranged to take place on a particular date)
                // match_status = 1 Played match
                // match_status = -1 Live match

                // $objs->team_a_score = '0';
                // $objs->team_b_score = '0';
                // $objs->winning_team = '0';
                // if ($objs->match_status !== '0') {
                //     $objs->team_a_score = !$match->homeParticipant->score ? '0' : $match->homeParticipant->score;
                //     $objs->team_b_score = !$match->awayParticipant->score ? '0' : $match->awayParticipant->score;
                //     $objs->winning_team = $this->getMatchWinner($match->homeParticipant, $match->awayParticipant, $match->stages, $playoff);
                // }

                $payload[] = $objs;
            }
        }
        return json_encode($payload);
    }

    public function getMatchWinner($home, $away, $stages, $playoff)
    {
        // Not practical. Need to revisit this mechanism.
        $winner = 0;
        if ($home->score > $away->score) { // if home winning
            $winner = $home->participantID;
        } elseif ($away->score > $home->score) { // if away winning
            $winner = $away->participantID;
        } elseif ($away->score === $home->score) { // if tie

            if ($playoff && isset($stages)) { // check match playoff and decide by penalty score
                if ($stages[0]->home_score > $stages[0]->away_score) { // if home winning penalty
                    $winner = $home->participantID;
                } elseif ($stages[0]->away_score > $stages[0]->home_score) { // if away winning penalty
                    $winner = $away->participantID;
                } else {
                    $winner = 0;
                }
            } else {
                $winner = 0;
            }
        } else {
            $winner = 0;
        }
        return $winner;
    }

    public function getMatches()
    {
        $api_url = 'https://api.statorium.com/api/v1';
        $api_key = '127d6a2d6457c957d257caaa0ffcb412';
        $season_id = 121;
        $method = 'matches';
        $url = $api_url . '/' . $method . '/?season_id=' . $season_id . '&apikey=' . $api_key;
        // $url = 'https://api.statorium.com/api/v1/matches/?season_id=40&apikey=127d6a2d6457c957d257caaa0ffcb412';

        $payload = [];
        $client = new Client();

        try {
            $res = $client->get($url)->getBody()->getContents();
            $calendar = json_decode($res)->calendar;
            $matchDays = $calendar->matchdays;

            foreach ($matchDays as $key => $items) {
                $object = new \stdClass;
                $object->stage = $this->getMatchDayNameTranslation($items->matchdayName);
                $object->playoff = $items->matchdayPlayoff;
                $object->matches = isset($items->matches) ? $items->matches : [];

                $payload[] = $object;
            }

            return $payload;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getMatchDayNameTranslation($name)
    {
        $nameToLower = strtolower($name);
        $mdHumanName = array(
            'matchday 1' => '1',
            'matchday 2' => '2',
            'matchday 3' => '3',
            '1/8 final' => 'Round of 16',
            '1/4 final' => 'Quarter Finals',
            '1/2 final' => 'Semi Finals',
            'final' => 'Final',
            'third place' => 'Third Place'
        );
        $translatedName = isset($mdHumanName[$nameToLower]) ? $mdHumanName[$nameToLower] : $name;
        return $translatedName;
    }
}
