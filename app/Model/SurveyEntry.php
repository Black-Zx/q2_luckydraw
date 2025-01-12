<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class SurveyEntry extends Model
{
    protected $table = 'survey_entry';

    protected $fillable = [
        'user_id', 'Q1', 'Q2', 'Q3', 'Q4', 'Q5', 'Q6', 'Q7', 'Q8', 'Q9', 'Q10', 'Q11', 'Q12', 'Q13', 'Q14', 'Q15', 'Q1_updated', 'Q2_updated', 'Q3_updated', 'Q4_updated', 'Q5_updated', 'Q6_updated', 'Q7_updated', 'Q8_updated', 'Q9_updated', 'Q10_updated', 'Q11_updated', 'Q12_updated', 'Q13_updated', 'Q14_updated', 'Q15_updated', 'score', 'trackip', 'status'
    ];
}