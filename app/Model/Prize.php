<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Prize extends Model
{
    protected $table = 'prize';

    protected $hidden = [
        'is_prize', 'quantity', 'rate_min', 'rate_max', 'type', 'created_at', 'updated_at'
    ];
}
