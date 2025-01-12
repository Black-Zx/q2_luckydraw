<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $table = 'question';

    protected $fillable = [
        'id', 'question', 'a_1', 'a_2', 'a_3', 'a_4', 'answer', 'status'
    ];
}
