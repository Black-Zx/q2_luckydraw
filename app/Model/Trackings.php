<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Trackings extends Model
{
    protected $table = 'trackings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'intent', 'ip_address', 'uuid'
    ];
}
