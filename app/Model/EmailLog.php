<?php
namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $table = 'email_log';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'prize_id', 'tng_amount', 'tng_value', 'result', 'status'
    ];

    protected $hidden = [
        // 'week', 'user_id'
    ];
}
