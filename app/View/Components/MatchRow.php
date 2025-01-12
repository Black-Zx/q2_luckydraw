<?php

namespace App\View\Components;

use Illuminate\View\Component;

class MatchRow extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public $message; // message is a MUST
    public $matchData;
    public $countryA;
    public $countryB;

    public function __construct($matchData, $countryList, $message)
    {
        $this->matchData = $matchData;
        $this->message = $message;

        foreach ($countryList as $country) {
            if($country->id == $matchData->team_a_id){
                $this->countryA = $country;
            } else if($country->id == $matchData->team_b_id){
                $this->countryB = $country;
            };

            if(isset($countryA) && isset($countryB)){
                break;
            };
        };
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.match-row');
    }
}
