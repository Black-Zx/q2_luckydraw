<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Arr;
use Carbon\Carbon;

class MatchTable extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public $message; // message is a MUST
    public $stages;
    public $matchData;
    public $countryA;
    public $countryB;
    public $results;
    public $teams;
    public $scores;

    public function __construct($matchData, $countryList, $stageList, $userScores, $message)
    {
        $this->matchData = $matchData;
        $this->message = $message;
        $this->stages = $stageList;
        $this->teams = $countryList;
        $this->scores = $userScores;

        foreach ($matchData as $match) {
            $obj = new \stdClass;
            $obj->name = '';
            $obj->label = '';

            $date = $match->match_date;
            // $date = Carbon::createFromFormat('Y-m-d H:i:s', $tz, 'UTC');
            $date_now = Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now(), 'Asia/Singapore');
            // $newDate = $date->setTimezone('Asia/Singapore');
            $newDate = $date;

            $dateArr = explode(' ', $newDate);
            $dateH = Carbon::parse($dateArr[0])->format('l d M Y');
            $match->team_a = $this->getTeam($match->team_a_id) ? $this->getTeam($match->team_a_id) : $obj;
            $match->team_b = $this->getTeam($match->team_b_id) ? $this->getTeam($match->team_b_id) : $obj;
            $match->time_h = Carbon::parse($dateArr[1])->format('H:i');
            $match->user_scores = $this->getUserScores($match->match_id);
            $match->stage_name_h = $match->stage_name ? explode(" ", $match->stage_name)[1] : '';
            $match->stop_submission = $date_now->diffInHours($date) < 1 ? true : false;
            $this->results[$match->stage][$dateH][] = $match;
        }
    }

    public function getUserScores($mid)
    {
        $scores = $this->scores;
        foreach ($scores as $score) {
            if ($score->match_id === $mid) {
                return $score;
            }
        }
    }

    public function getTeam($id)
    {
        $teams = $this->teams;
        foreach ($teams as $team) {
            if ($team->id === $id) {
                $team->label = str_replace(' ', '-', strtolower($team->name));
                return $team;
            }
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.submission.table-content');
    }
}
