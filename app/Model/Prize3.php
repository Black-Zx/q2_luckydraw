<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Prize3 extends Model
{
    protected $table = 'prizes3';

    protected $hidden = [
        'is_prize', 'quantity', 'rate_min', 'rate_max', 'type', 'created_at', 'updated_at'
    ];
}
