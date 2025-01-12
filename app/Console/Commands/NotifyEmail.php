<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Mail;
use App\Model\EmailLog;

class NotifyEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email according to DB table log';

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
        $pending = EmailLog::where('status', 0)->orderBy('created_at', 'asc')->first();

        if($pending) {
            $this->info('pending data to notify: '.$pending);
            $this->info('attempt to send mail');
            
            // send email
            $tng_amount = $pending->tng_amount;
            $tng_value = $pending->tng_value;
            $now = $pending->created_at;
            // $result = $this->custom_sendMail('Kent', 'fongchan2002@gmail.com', $tng_amount, $tng_value, $now);
            $result = $this->custom_sendMail('KC Yong', 'kc.yong@comma.com.my', $tng_amount, $tng_value, $now);
            
            if($result){
                $this->info('email sent result: '.$result);

                // mail is sent, update log
                $update = EmailLog::where('id', $pending->id)->update(['result' => $result, 'status' => 1]);

                if($update) $this->info('email log DB updated');
            };
        };

        $this->info('notify email complete.');
    }

    private function custom_sendMail($name, $email, $tng, $value, $now){
        // process date
        // $today = date("Y-m-d H:i:s");
        // if($tng == 100 || $tng == 50){
            // create and send mail
            $data = array('today' => $now, 'tng' => $tng, 'value' => $value);

            // use:: pass variable into Mail function($message)
            Mail::send(['html' => 'emails.notify'], $data, function ($message) use ($email, $name) {
                $message->to($email, $name)
                    ->subject('Bat-One: TnG reaching limit');
                $message->from('noreply@powerofthescore.com', 'Power of the Score');
            });
            $result = "Mail sent";
        // };

        return $result;
    }
}