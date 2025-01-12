<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    protected $table = 'terms';

    protected $fillable = [
        'id', 'user_id', 'name', 'phone', 'signature'
    ];
}
