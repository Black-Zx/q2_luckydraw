<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\User;
use Illuminate\Support\Facades\Hash;

class UpdatePassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'password:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'For convenience of setting new password';

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
    	// update/reset password for the first time
        $result = User::where('username', 'test1111')
            ->update(['password' => Hash::make('BV13Rn9s')]);

        $this->info('update password complete.');
    }
}