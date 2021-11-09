<?php defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/third_party/RestController.php';
require APPPATH . '/third_party/Format.php';
use chriskacerguis\RestServer\RestController;
use Lcobucci\JWT;

/**
 * Serves as a base for all Controllers
 * 
 * @author Mark Wickine 2020-01-13
 */

class Base extends RestController{

    protected $guzzle; //Guzzle instance https://github.com/guzzle/guzzle
    protected $auth; //The claims from the JWT token.
    protected $conf;

    function __construct(){
        parent::__construct();
    }

    /**
     * Initialize resources. To be done when authorized.
     */
    protected function _init(){
        date_default_timezone_set('America/Chicago');
        $this->guzzle = new GuzzleHttp\Client();
        $this->conf = $this->config->item("asl");
        $this->load->model('objects/User_Object', 'user_object');
    }

    protected function verifyAndLoadUser(){
        $token = $this->decryptAuthToken();
        $token = explode("|", $token);
        if(!$this->user_object->loadFromEmail($token[1]))
            $this->notAcceptable("Not Authorized 52");
        if($token[0] !== $this->user_object->getToken())
            $this->notAcceptable("Not Authorized 53");
    }


    /**
     * Check if the Authorization header exists and return it, else return false.
     * 
     * @return string|false
     */
    private function _authTokenExists(){
        $headers = $this->_head_args;
        if(!empty($headers) && is_array($headers) && 
            array_key_exists('Authorization', $headers)
        ){
            $authHeader = $headers['Authorization'];
            if(!preg_match('/^Bearer /', $authHeader)){
                $this->notAcceptable("Not Authorized 50");
            } else {
                return trim(preg_replace('/^Bearer /', '', $authHeader));
            }
        }
        return false;
    }

    /**
     * A quick method for returning unaceptable information.
     * 
     * @param string $message The error message to display.
     */
    protected function notAcceptable($message){
        $this->response([
            'status' => false,
            'message' => $message
        ], 406);
    }

    /**
     * Call array is set from Common_Helper and return unaceptable if not
     * @see Common_Helper
     */
    protected function validateArrayIsset($parms, $keys){
        if(!arrayIsset($parms, $keys)){
            $this->notAcceptable("Missing parameters");
        }
    }


    /**
     * Goal here is to set a global rate limit
     * and also a limit for individual ipAddresses
     */
    protected function basicRateLimit(){

        $ipAddress = $this->input->ip_address();
        if($ipAddress === "0.0.0.0" || $ipAddress === false){
            $this->notAcceptable("CODE 703");
        }
        
        if(!property_exists($this, 'model')){
            $this->load->model('Helper_Model', 'model');
        }

        $settings = $this->model->getSettings(['RATE_LIMIT']);

        // Check personal IP rate limit
        if($ipTracker = $this->model->getRowWhere('t_guest_ip_tracker', ['ip_address' => $ipAddress])){
            $lastRequest = new DateTime($ipTracker['last_request']);

            if($this->_checkInterval($lastRequest, $settings['RATE_LIMIT,IP_ADDRESS,INTERVAL'])){
                // They are good, interval has passed. Reset their count to 0
                $this->model->updateGuestIpTracker($ipAddress, 0);
            } elseif((int)$ipTracker['count'] <= (int)$settings['RATE_LIMIT,IP_ADDRESS']){
                // They are still within the count limit for the interval. Do count++
                $this->model->updateGuestIpTracker($ipAddress, (int)$ipTracker['count'] + 1);
            } else {
                // They are not in the bounds of rate limit
                $this->notAcceptable("CODE 704");
            }
        } else {
            $this->model->ins('t_guest_ip_tracker', ['ip_address' => $ipAddress]);
        }
        
        // Now check global rate limiting.
        $lastRequest = $settings['RATE_LIMIT,GLOBAL,DATETIME'];
        $curCount = $settings['RATE_LIMIT,GLOBAL,COUNT'];
        if($this->_checkInterval($lastRequest, $settings['RATE_LIMIT,GLOBAL,INTERVAL'])){
            // Global interval has passed, reset the count
            $this->model->updateSettingValue('RATE_LIMIT,GLOBAL,COUNT', 0);
            $this->model->updateSettingValue('RATE_LIMIT,GLOBAL,DATETIME', sqlTimeStamp());
        } elseif($curCount <= $settings['RATE_LIMIT,GLOBAL']){
            // Global interval still good, do the count++
            $this->model->updateSettingValue('RATE_LIMIT,GLOBAL,COUNT', $curCount + 1);
            $this->model->updateSettingValue('RATE_LIMIT,GLOBAL,DATETIME', sqlTimeStamp());
        } else {
            // No good, we are out of request for the interval settings
            $this->notAcceptable("CODE 705");
        }
    }

    /**
     * Check if seconds elapsed are greater than the seconds given.
     */
    private function _checkInterval($lastDateTime, $secondsAllowed){
        $newDate = new DateTime();
        // $intervalChange = $newDate->diff($lastDateTime);
        // $elapsed = $intervalChange->format("%s");

        $elapsed = $newDate->getTimestamp() - $lastDateTime->getTimestamp();

        if($elapsed > $secondsAllowed){
            return true;
        }
        return false;
    }

    protected function decryptAuthToken(){
        if(!$token = $this->_authTokenExists()){
            $this->notAcceptable("Not Authorized 51");
        }
        return $this->decrypt($token);
    }

    /**
     * Encrypt with AES-256-CTR + HMAC-SHA-512
     * 
     * @param string $plaintext Your message
     * @param string $encryptionKey Key for encryption
     * @param string $macKey Key for calculating the MAC
     * @return string
     */
    protected function encrypt($plaintext){
        $encryptionKey = file_get_contents($this->conf['token_key1_location']);
        $macKey = file_get_contents($this->conf['token_key2_location']);
        $nonce = random_bytes(16);
        $ciphertext = openssl_encrypt(
            $plaintext,
            'aes-256-ctr',
            $encryptionKey,
            OPENSSL_RAW_DATA,
            $nonce
        );
        $mac = hash_hmac('sha512', $nonce.$ciphertext, $macKey, true);
        return base64_encode($mac.$nonce.$ciphertext);
    }

    /**
     * Verify HMAC-SHA-512 then decrypt AES-256-CTR
     * 
     * @param string $message Encrypted message
     * @param string $encryptionKey Key for encryption
     * @param string $macKey Key for calculating the MAC
     */
    protected function decrypt($message){
        $encryptionKey = file_get_contents($this->conf['token_key1_location']);
        $macKey = file_get_contents($this->conf['token_key2_location']);

        $decoded = base64_decode($message);
        $mac = mb_substr($decoded, 0, 64, '8bit');
        $nonce = mb_substr($decoded, 64, 16, '8bit');
        $ciphertext = mb_substr($decoded, 80, null, '8bit');

        $calc = hash_hmac('sha512', $nonce.$ciphertext, $macKey, true);
        if (!hash_equals($calc, $mac)) {
            throw new Exception('Invalid MAC');
        }
        return openssl_decrypt(
            $ciphertext,
            'aes-256-ctr',
            $encryptionKey,
            OPENSSL_RAW_DATA,
            $nonce
        );
    }

}