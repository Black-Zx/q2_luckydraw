<?php
namespace App\Http\Controllers;

use App\Model\User;
use App\Model\SpinUser;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LoginValidator extends Controller{
    
    public function __construct() {
        // $this->middleware(['auth:web']);
    }

    public function checkIfLocked($username, $type = 1) {
        $result = '';
        // if(!isset($type)) $type = 1;
        // $type = 1;

        if($type == 1){
            $thisUser = User::where('username', $username)->first();
        } else {
            $thisUser = SpinUser::where('username', $username)->first();
        };
        if(isset($thisUser)){
            if(isset($thisUser->locked_at)){
                // check if account locked
                $now = new DateTime();
                $lockedTime = new DateTime($thisUser->locked_at);
                $lockedTime->modify('+1 day');
                if($now < $lockedTime){
                    // account is locked
                    $result = 'Account Locked';
                } else {
                    // past the locked time, null locked_at to unlock account
                    if($type == 1){
                        User::where('username', $username)->update(['failed' => 0, 'locked_at' => NULL]);
                    } else {
                        SpinUser::where('username', $username)->update(['failed' => 0, 'locked_at' => NULL]);
                    };
                };
            } else {
                /*
                if($thisUser->locked_at != NULL && $thisUser->failed > 0){
                    // clear the failed attempt count, if any
                    if($type == 1){
                        User::where('username', $username)->update(['failed' => 0]);
                    } else {
                        SpinUser::where('username', $username)->update(['failed' => 0]);
                    };
                };
                */
            };
        };

        return $result;
    }

    public function clearLocked($username, $type = 1){
        if($type == 1){
            User::where('username', $username)->update(['failed' => 0]);
        } else {
            SpinUser::where('username', $username)->update(['failed' => 0]);
        };
    }

    public function recordFailed($username, $type = 1){
        $result = '';
        // if(!isset($type)) $type = 1;
        // $type = 1;

        // record failed login attempt
        if($type == 1){
            $thisUser = User::where('username', $username)->first();
        } else {
            $thisUser = SpinUser::where('username', $username)->first();
        };
        if(isset($thisUser)){
            // record failed attempt
            $totalFailed = $thisUser->failed + 1;
            $now = null;
            if($totalFailed < 5){
                // record failed count
                if($type == 1){
                    $update = User::where('username', $username)->update(['failed' => $totalFailed]);
                } else {
                    $update = SpinUser::where('username', $username)->update(['failed' => $totalFailed]);
                };
            } else {
                // lock account for 24hours
                $now = new DateTime();
                // $now->modify('+1 day');
                if($type == 1){
                    $update = User::where('username', $username)->update(['failed' => $totalFailed, 'locked_at' => $now]);
                } else {
                    $update = SpinUser::where('username', $username)->update(['failed' => $totalFailed, 'locked_at' => $now]);
                };

                $result = "Account Locked";
            };
        };

        return $result;
    }
}