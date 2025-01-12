<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Crypt;

class MyEncryption extends Controller{
	public $SALT = 'salt';
	public $ITERATIONS = 999;

	public function __construct() {
		// $this->middleware(['auth:web']);
	}

	public function init(){
		$passPhrase = sha1(time()*rand(0, 100));
        $IV = substr($passPhrase, 0, 16);
        $hash1 = hash_pbkdf2("sha256", $passPhrase, $this->SALT, $this->ITERATIONS, 64);
        // store hash
        Session::put('passPhrase', $passPhrase);
        Session::put('IV', $IV);
        Session::put('hash1', $hash1);
        Session::put('hash2', '');
	}

	public function get($key){
		return Session::get($key);
	}

	public function getSalt(){
		return $this->SALT;
	}
	public function getIteration(){
		return $this->ITERATIONS;
	}

	public function decrypt($data){
		$IV = Session::get('IV');
        $key = Session::get('hash1');
        $thisData = json_decode($this->phpDecrypt($key, $IV, $data), false);

        $result = $this->keyCheck($thisData->checksum, $thisData->checksum2);

        if($result){
        	return $thisData;
        };

        return false;
	}

	public function package($callbackData){ // previously named as callbackManager, encrypt data for callback
        // retrieve the new keys
        $passPhrase = Session::get('passPhrase');
        $IV = Session::get('IV');
        $key = Session::get('hash1');
        $key2 = Session::get('hash2');

        // data for callback, stringify for encryption
        $tempData = json_encode(['checksum' => $key, 'checksum2' => $key2, 'data' => $callbackData]);
        // encrypt data for callback
        $result = $this->phpEncrypt($key, $IV, $tempData);
        return ['data' => $result, 'p' => $passPhrase, 'i' => $IV];
    }

	private function keyCheck($key, $key2){
        $thisKey = Session::get('hash1');
        $thisKey2 = Session::get('hash2');

        $valid = false;
        if($thisKey == $key && $thisKey2 == $key2){
            $valid = true;
            $this->keyUpdate();  
        };

        return $valid;
    }
    private function keyUpdate(){
        $thisKey = Session::get('hash1');
        $thisKey2 = Session::get('hash2');

        // update hash2
        Session::put('hash2', $thisKey);

        // create new key for hash1
        $passPhrase = sha1(time()*rand(0, 100));
        $IV = substr($passPhrase, 0, 16);
        $thisKey = hash_pbkdf2("sha256", $passPhrase, $this->SALT, $this->ITERATIONS, 64);
        
        // store hash1
        Session::put('passPhrase', $passPhrase);
        Session::put('IV', $IV);
        Session::put('hash1', $thisKey);
    }
    private function phpEncrypt($key, $IV, $plainText){
        // $key = hash_pbkdf2("sha256", $passphrase, SALT, ITERATIONS, 64);
        $encryptedData = openssl_encrypt($plainText, 'AES-256-CBC', hex2bin($key), OPENSSL_RAW_DATA, $IV);
        return base64_encode($encryptedData);
    }
    private function phpDecrypt($key, $IV, $encryptedTextBase64){
        $encryptedText = base64_decode($encryptedTextBase64);
        // $key = hash_pbkdf2("sha256", $passphrase, SALT, ITERATIONS, 64);
        $decryptedText = openssl_decrypt($encryptedText, 'AES-256-CBC', hex2bin($key), OPENSSL_RAW_DATA, $IV);
        return $decryptedText;
    }
}