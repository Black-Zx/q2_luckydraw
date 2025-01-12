<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Spin extends Model
{
    protected $table = 'spin_record';

    protected $fillable = [
        'user_id', 'prize_id', 'tng_id', 'scope', 'trackip', 'status'
    ];

    public function prize()
    {
        return $this->belongsTo(Prize::class)->orderBy('weight', 'desc');
    }

    public function prize2()
    {
        return $this->belongsTo(Prize2::class, 'prize_id')->orderBy('weight', 'desc');
        // return $this->belongsTo(Individual::class, 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
