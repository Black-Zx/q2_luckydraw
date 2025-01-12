<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\User;

class ReallocateSpin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reallocate:spin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reallocate daily chances and also distribute daily spins';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // update all user's available and chance to 1
        // $update = User::where('available', 0)->update(['available' => 1]);
        // $update = User::where('chance', 0)->update(['chance' => 1]);
        $update = User::query()->where('chance', 0)
                    ->update(['chance' => 1]);

        // for daily popup, once a day
        // $update = User::query()->where('remember_token', '<>', NULL)
                    // ->update(['remember_token' => NULL]);

        $this->info('Spin re-allocation complete.');
    }
}