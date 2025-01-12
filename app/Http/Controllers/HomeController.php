<?php

namespace App\Http\Controllers;

// use Auth;
// use Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Validator;
use DateTime;
use stdClass;
use Illuminate\Http\Request;
use App;
use App\Model\User;
use App\Model\Spin;
use App\Model\Trackings;
use App\Model\Prize;
use App\Model\Prize2;
use App\Model\Prize3;
use App\Model\Term;
use Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Encryption\DecryptException;

use \Arhey\FaceDetection\Facades\FaceDetection;


use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public $successStatus = 200;
    public $Encryption;
    public $LoginValidator;
    public $Helper;

    public function __construct(MyEncryption $MyEncryption, LoginValidator $validator, Helper $Helper)
    {
        $this->Encryption = $MyEncryption;
        $this->LoginValidator = $validator;
        $this->Helper = $Helper;
        // $this->middleware(['auth:web']);
    }

    public function callAction($method, $parameters)
    {
        return parent::callAction($method, array_values($parameters));
    }

    public function login(Request $request)
    {
        $BaseUrl = url('/');
        $locale = session()->has('locale') ? App::getLocale() : 'en';

        $this->Encryption->init();

        $params = [
            'BaseUrl' => $BaseUrl,
            'iv' => $this->Encryption->get('IV'),
            'salt' => $this->Encryption->getSalt(),
            'iterations' => $this->Encryption->getIteration(),
            'passPhrase' => $this->Encryption->get('passPhrase'),
            'locale' => $locale
        ];

        return view('home.login', $params);
    }

    public function end(Request $request)
    {
        return view('home.end');
    }

    public function resetpass(Request $request)
    {
        $BaseUrl = url('/');

        $thisEmail = 'invalid';
        // has token
        // $decrypted = Crypt::decryptString($request->pass_token);
        try {
            $decrypted = Crypt::decryptString($request->pass_token);
        } catch (DecryptException $e) {
            //
            $decrypted = false;
        };

        if ($decrypted) {
            $thisReset = PasswordReset::where('token', $decrypted)->first();
            if ($thisReset) {
                // reset password request exist
                $thisEmail = $thisReset->email;
            };
        };

        $params = [
            'BaseUrl' => $BaseUrl,
            'email' => $thisEmail,
            'thisApi' => 'update_password'
        ];

        return view('home.resetpass', $params);
    }

    public function dashboard(Request $request)
    {
        $BaseUrl = url('/');
        $user = Auth::guard('web')->user();
        // return $user->batch;
        if (!$user) {
            return redirect()->route('home.login');
        };

        $end = $this->Helper->campaign_end();

          // get different pool prize list
        if ($user->batch == 1) {
            $pool = Prize::select(['id', 'name'])->get()->toArray();
        } elseif ($user->batch == 2) {
            $pool = Prize2::select(['id', 'name'])->get()->toArray();
            // return 2;
        // } else {
        //     $pool = Prize3::select(['id', 'name'])->get()->toArray();
        //     // return 3;
        }
        $prizes = $pool;
        // return $prizes;
        

        $this->Encryption->init();

        // get prize list
        // $prizes = Prize::select(['id', 'name'])->get()->toArray();

        // $spin_entitled = Spin::where('user_id', $user->id)->orderby('prize_id', 'asc')->first();
        // $spin_results = Spin::where('user_id', $user->id)->orderby('created_at', 'asc')->limit($user->max_chance)->get();

        if ($user->chance <= 0) {
            $spinCount = Spin::where('user_id', $user->id)->get()->count();
            if($spinCount > 0) return redirect()->route('home.term');
            // $params = [
            //     'BaseUrl' => $BaseUrl,
            //     'spin_entitled' => $spin_entitled,
            //     'prize' => $spin_results,
            //     'user_id' => $user->id,
            // ];
            // return view('home.term', $params);
        }

        $params = [
            'BaseUrl' => $BaseUrl,
            'iv' => $this->Encryption->get('IV'),
            'salt' => $this->Encryption->getSalt(),
            'iterations' => $this->Encryption->getIteration(),
            'passPhrase' => $this->Encryption->get('passPhrase'),
            'prizes' => $prizes,
            'chance' => $user->chance,
            'max_chance' => $user->max_chance,
            'end' => $end
        ];

        return view('home.dashboard', $params);
    }

    public function thankyou(Request $request)
    { // Thank you page
        $BaseUrl = url('/');
        $user = Auth::guard('web')->user();
        if (!$user) {
            return redirect()->route('home.login');
        };
        $score = $request->session()->get('score');
        $spin_entry = $request->session()->get('spin_entry');
        $previous = $request->session()->get('previous');
        $entitled = $request->session()->get('entitled');

        // clean up to prevent hack
        // $request->session()->flush();

        $params = [
            'BaseUrl' => $BaseUrl,
            'score' => $score,
            'spin_entry' => $spin_entry,
            'previous' => $previous,
            'entitled' => $entitled
        ];

        return view("home.thankyou", $params);
    }

    /*
    public function test_sendMail()
    {
        // process date
        // $today = date("Y-m-d H:i:s");
        // $date = "2022-04-29 00:00:00";
        // $result = 'nothing to report';
        // if($tng == 1000 || $tng == 500 || $tng == 300 || $tng == 200 || $tng == 100){
        // create and send mail
        // $data = array('name' => "TnG Reloc Laravel", 'today' => $today);
        $date = date("Y-m-d H:i:s");
        $data = array('url' => url('/resetpass'), 'today' => $date);

        $name = 'Kent Test';
        $email = 'fongchan2002@gmail.com';

        // use:: pass variable into Mail function($message)
        $result = Mail::send(['html' => 'emails.reset'], $data, function ($message) use ($email, $name) {
            $message->to($email, $name)
                ->subject('Power of The Score: Reset Password');
            $message->from('noreply@powerofthescore.com', 'Power of the Score');
        });
        // $result = "Mail sent";
        // };

        return '<pre>' . print_r($result) . '</pre>';
    }
*//*
    public function throttle_test(Request $request) {
        $BaseUrl = url('/');
        $user = Auth::guard('web')->user();
        if (!$user) {
            return redirect()->route('home.login');
        };
        $week = $this->Helper->getWeek();

        $params = [
            'BaseUrl' => $BaseUrl,
            'week' => $week
        ];

        return view('home.throttle', $params);
    }
*/
    private function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    private function calcRank($ranking)
    {
        $result = ''; //strval($ranking);

        $lastDigit = $ranking % 10;
        if ($lastDigit == 1) {
            $result = 'st';
        } else if ($lastDigit == 2) {
            $result = 'nd';
        } else if ($lastDigit == 3) {
            $result = 'rd';
        } else {
            $result = 'th';
        };

        $secondDigit = floor($ranking / 10) % 10;
        if ($secondDigit == 1) {
            $result = 'th';
        };

        return $result;
    }

    public function term(Request $request){
        $BaseUrl = url('/');
        $user = Auth::guard('web')->user();
        if (!$user) {
            return redirect()->route('home.login');
        };
        // return $user;

        $spin_entitled = Spin::where('user_id', $user->id)->where('status', 1)->orderby('prize_id', 'asc')->first();
        // return $spin_entry;
        // $spin_count = Spin::where('user_id', $user->id)->orderby('created_at', 'asc')->get()->count();
        // return $spin_count;

        // $total_spin = Spin::where('trackip', $this_ip)
        // ->where('created_at', '>=', $today_start)->where('created_at', '<=', $today_end)->get()->count();
        // if($total_spin >= 10) $is_spam = true;

        // if ($spin_count <= $user->max_chance) {
        // // if ($spin_count >= $user->max_chance) { //spin more than chances
        // // if ($user->max_chance >= $spin_count) { //chance more than spins 
        //     return 99;
        //     // return $spin_count;
        //     // $spin_results = Spin::where('user_id', $user->id)->orderby('created_at', 'asc')->get();
        // } else {
            // return $spin_count;
            // $others = Spin::where('user_id', $user->id)->orderby('created_at', 'asc')->limit($user->max_chance)->get();
        // }
        // $spin_results = $others;
        // return $spin_results;

        if($user->batch == 1){
            $prize_name_list = Spin::join('prize', 'spin_record.prize_id', '=', 'prize.id')
                                ->limit($user->max_chance)->orderBy('spin_record.id', 'asc')
                                ->where('spin_record.user_id',$user->id)
                                ->where('spin_record.status', 1)
                                ->pluck('prize.name')->toArray();
            // Prize::select('name')->where('id', $spin_results->prize_id)->first();
        } else {
            $prize_name_list = Spin::join('prizes2', 'spin_record.prize_id', '=', 'prizes2.id')
                                ->limit($user->max_chance)->orderBy('spin_record.id', 'asc')
                                ->where('spin_record.user_id',$user->id)
                                ->where('spin_record.status', 1)
                                ->pluck('prizes2.name')->toArray();
        };

        if(!$prize_name_list){
            $prize_name_list = [];
        };

        // clean up to prevent hack
        // $request->session()->flush();

        $params = [
            'BaseUrl' => $BaseUrl,
            // 'prize' => $spin_entry,
            'spin_entitled' => $spin_entitled,
            'prize' => $prize_name_list,
            'user_id' => $user->id,
        ];
        
        return view('home.term', $params);
    }

    public function term_store(Request $request){
        $validated = $request->validate([
            'name' => 'required',
            'contact' => 'required',
        ]);

        if ( !$validated ) {
            return redirect()->back()->withErrors('Missing paremeters');
        }

        $folderPath = public_path('img/uploads/'); 
        $image_parts = explode(";base64,", $request->image);
        $file = base64_decode($image_parts[1]);
        // $imageName = uniqid() . '.jpg';
        $imageName = $this->generateRandomString(32).'.'.'jpg';
        $imageFullPath = $folderPath.$imageName;
        //  return $imageFullPath;
        file_put_contents($imageFullPath, $file);

        $term = new Term;
        $term->user_id = $request->user_id;
        $term->name = $request->name;
        $term->phone = $request->contact;
        $term->signature = $imageName;
        $term->save();

        return response()->json('Success');
    }


    /* ----------- API RELATED ---------- */
    public function login_api(Request $request){
        $thisData = $this->Encryption->decrypt($request->data);

        if($thisData){
            // setup locale upon login
            $lang = 'en'; //$thisData->formData->language;
            session()->put('locale', $lang);
            App::setLocale(session()->get('locale'));

            $ori_session = session()->getId();

            // check if user is locked due to password error
            $valid = $this->LoginValidator->checkIfLocked($thisData->formData->username, 1);
            if(strlen($valid) > 0){
                // account locked
                $result = $this->Encryption->package(['error' => $valid]);
                return response()->json(['result' => $result], 401);
            };

            // check if user account is still active
            $active = User::where('username', $thisData->formData->username)->first();
            if ($active && $active->status == 0) {
                $result = 2;
                $result = $this->Encryption->package(['result' => $result]);
                return response()->json(['result' => $result], 401);
            };
            
            // $request->validate(['username', 'password']);
            $loginuser = Auth::guard('web')->attempt(['username' => $thisData->formData->username, 'password' => $thisData->formData->password], 0);

            if ($loginuser) {
                // get user
                $user = Auth::guard('web')->user();
                $this->LoginValidator->clearLocked($thisData->formData->username, 1);
                $result = 1;

                $result = $this->Encryption->package(['result' => $result]);
                return response()->json(['result' => $result], 200);
            } else {
                // login not successful
                $valid = $this->LoginValidator->recordFailed($thisData->formData->username, 1);
                if(strlen($valid) > 0){
                    // account locked
                    $result = $this->Encryption->package(['error' => $valid]);
                    return response()->json(['result' => $result], 401);
                };

                // return response()->json(['error' => 'login_error'], 401);
                $result = $this->Encryption->package(['error' => 'Login Error']);
                return response()->json(['result' => $result], 401);
            };

            // If the login attempt was unsuccessful we will increment the number of attempts
            // to login and redirect the user back to the login form. Of course, when this
            // user surpasses their maximum number of attempts they will get locked out.
            // $this->incrementLoginAttempts($request);
        };

        // return response()->json(['error' => 'Unknown Error'], 401);
        $result = $this->Encryption->package(['error' => 'Unknown Error']);
        return response()->json(['result' => $result], 401);
    }

    public function selection(Request $request){
        $test = DB::table('users_wheel')
        ->select('region', 'ASE_id', 'outlet_id', 'outlet_name')
        ->get();

        $region = DB::table('users_wheel')
        ->select('region')
        ->distinct()
        ->get();

        $ase = DB::table('users_wheel')
        ->select('ASE_id')
        ->distinct()
        ->get();

// return $test;
        //  $get_region = $conn->query("SELECT DISTINCT region FROM users_wheel"); 
		// 				while($region_row = $get_region->fetch_assoc()): 
		// <!-- // 				<option value=" $region_row['region']; </option> -->
		// <!-- // 				 endwhile; -->

        $param =[
            'users' => $test,
            'regions' => $region,
            'ases' => $ase,
        ];
       
        return view("selection", $param);
    }

    public function ase_id(Request $request){
        // return $request->region;
        $region = $request->region;

        $ase =  DB::table('users_wheel')
        ->select('ASE_id')
        ->distinct()
        ->where('region', $region);
        // ->get();

        // return $ase;
        // $sql = "SELECT DISTINCT ASE_id FROM users_wheel WHERE region = '$region'";

        // $result = $conn->query($sql);


        // $sql = $conn->query("SELECT * FROM users_wheel WHERE region = '$region'");
        // if($sql->num_rows == 1) {
        //     $row = mysqli_fetch_assoc($sql);
        //     if($row['role'] == "Admin") {
        //         setcookie("id", $row['userId'], time()+60*60*24*30, "/", NULL);

        // Output options for the name dropdown
        // if ($result->num_rows > 0) {
        //     echo "<option value=''></option>";
        //     while($row = $result->fetch_assoc()) {
        //         echo "<option value='".$row["ASE_id"]."'>".$row["ASE_id"]."</option>";
        //     }
        // } else {
        //     echo "<option value=''>Region Found</option>";
        // }

        // return Response::json($states->get(['id', 'description']));
        // return response()->json(['ase' => $ase]);
        return response()->json($ase->get(['ASE_id']));
    }

    public function outlet_name(Request $request){
        $name =  DB::table('users_wheel')
        ->select('outlet_name')
        ->distinct()
        ->where('ASE_id', $request->ASE_id);

        return response()->json($name->get(['ASE_id']));
    }
}
