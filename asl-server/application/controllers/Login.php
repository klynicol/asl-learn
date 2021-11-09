<?php defined('BASEPATH') OR exit('No direct script access allowed');
include_once APPPATH . 'controllers/Base.php';
include_once APPPATH . 'libraries/Cipher_Sweet.php';

/**
 * Controller to generate a solid auth token upon successfull login.
 * This controller should also handle registering???
 */
class Login extends Base{
    public function __construct(){
        parent::__construct();
        parent::_init();
    }

    public function test_get(){
        
    }

    public function login_post(){
        $this->basicRateLimit();
        $formData = $this->post(null, true);
        if(!$this->_isValidEmail($formData['email']))
            $this->notAcceptable("Password or username is incorrect!");
        if(!$this->user_object->loadFromEmail($formData['email']))
            $this->notAcceptable("Password or username is incorrect!");
        if(!$this->user_object->verifyPassword($formData['password']))
            $this->notAcceptable("Password or username is incorrect!");
        // $token = $this->_generateToken($this->user_object->id, $this->user_object->username);
        $this->user_object->login();
        //Random token that's saved into the user table
        $token = $this->user_object->getToken();
        $token .= "|" . $this->user_object->email;
        $token = $this->encrypt($token);
        $this->response([
            'status' => true,
            'message' => 'User loaded successfully!',
            'data' => [ 
                'user' => $this->user_object->toJsonReady(),
                'token' => $token
            ]
        ], 200);
    }

    public function register_post(){
        $this->basicRateLimit();
        $formData = $this->post(null, true);
        //Validate. The frontend should validate but lets do it again for fun!
        $this->_validateRegisterCreds($formData);
        $this->load->model('Helper_Model', 'helper_model');
        if($this->helper_model->userEmailExists($formData['email'])){
            // TODO with blind index being limited we need a different method of checking emails
            // not sure what that will look like right now.
            $this->notAcceptable("Email already exists");
        }
		// $this->user_object->email = $formData['email'];
		$this->user_object->hashPassword($formData['password']);
        unset($formData['password']);
        foreach($formData as $field => $value){
            // Cycle through and add what we find.
            if(property_exists($this->user_object, $field)){
                $this->user_object->$field = $value;
            }
        }
        $this->user_object->create_date = new DateTime();
        $this->user_object->last_login_date = new DateTime();
        $this->user_object->last_activity_date = new DateTime();
		$this->user_object->insertThis();
        $this->response([
            'status' => true,
            'message' => "User created succesfully!",
            'data' => [ 'user' => $this->user_object->toJsonReady() ]
        ], 200);
    }

    public function facebookRegister_post(){

    }

    public function googleRegister_post(){
        $config = $this->config->item('pcron')['google_oath'];
        $parms = [
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect_uri'], //This is where the response is sent!!
            'response_type' => 'code',
            'scope' => 'email profile openid',
            'state' => 'testing_state_value',
            'nonce' => '0394852-3190485-2490358'
        ];
        $response = $this->guzzle->request('GET', 'https://accounts.google.com/o/oauth2/v2/auth', $parms);
        $this->response([
            'status' => true,
            'data' => $response->getBody()
        ], 200);
    }

    public function facebookLogin_post(){

    }

    public function googleLogin_post(){

    }

    /**
     * The first step to a forgotten password situation
     */
    public function forgotPasswordStepOne_post(){
        $this->basicRateLimit();
        $formData = $this->post(null, true);
        $this->validateArrayIsset($formData, ['email']);
        if(!$this->_isValidEmail($formData['email'])){
            $this->notAcceptable("Not a valid email address.");
        }
        if(!$this->user_object->loadFromEmail($formData['email'])){
            $this->notAcceptable("Not a valid email address.");
        }

        $this->load->model('Helper_Model', 'model');

        // Generate a temporary token so we can verify the link from the email
        $token = generateRandomString(16);
        $insData = [
            'type' => 'EMAIL_RESET',
            'identifier' => $this->user_object->id,
            'token' => $token
        ];
        $this->model->ins('t_temp_token', $insData);

        include APPPATH . 'libraries/PHP_Mailer.php';
        $mail = PHPMailerFactory::create();

        $body = "<h4>Password reset request from ASL-learn.com</h4>";
        $body .= "Follow this link to reset your password.<br>";
        $body .= "https://asl-learn.com/asl-server/bang/ooof?y={$this->user_object->id}&n={$token}<br>";
        $body .= "If it was not you who requested a password reset, please safely ignore this email.";

        $mail->setFromEase('noreply@asl-learn.com', 'No Reply');
        $mail->setContentEase('asl-learn.com Password Reset', $body);
        if(!$mail->sendToEase([
            "{$this->user_object->email}, {$this->user_object->first_name} {$this->user_object->last_name}"
        ])){
            $this->response([
                'status' => false,
                'msg' => "Email did not send, please contact admin@asl-learn.com"
            ]);
        };
    }

    /**
     * The second step to resetting a password.
     */
    public function forgotPasswordStepTwo_post(){
        $this->basicRateLimit();
        $formData = $this->post(null, true);
    }

    public function forgotUsername_get(){

    }

    /**
     * Sweet, a new user! Let get them set up
     */
    private function _createUser(){

    }

    /**
     * If username and password is coming from a form, let's validaate
     * the whole kit and kabudable.
     */
    private function _validateRegisterCreds($data){
        if(!isset($data['email']))
            $this->notAcceptable("Email is not set.");
        if(!isset($data['password']))
            $this->notAcceptable("Password is not set.");
        if(!$this->_isValidEmail($data['email']))
            $this->notAcceptable("Not a valid email address.");
        if(!$this->_isValidPassword($data['password']))
            $this->notAcceptable("Password is not valid. Minimum eight characters, maximum eighty characters, at least one upper case English letter, one lower case English letter, one number and one special character.");
    }

    /**
     * A username is a unique identifier given to accounts in websites and social media.
     * 
     * @see https://ihateregex.io/expr/username
     */
    private function _isValidUsername($username){
        if(preg_match('/^[a-z0-9_-]{3,15}$/', $username))
            return true;
        return false;
    }

    /**
     * Minimum eight characters, at least one upper case English letter, 
     * one lower case English letter, one number and one special character.
     * 
     * @see https://ihateregex.io/expr/password.
     */
    private function _isValidPassword($password){
        if(preg_match('/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$ %^&*-]).{8,80}$/', $password))
            return true;
        return false;
    }

    /**
     * Minimum eight characters, at least one upper case English letter, 
     * one lower case English letter, one number and one special character.
     * 
     * @see https://ihateregex.io/expr/email.
     */
    private function _isValidEmail($email){
        if(preg_match('/[^@ \t\r\n]+@[^@ \t\r\n]+\.[^@ \t\r\n]+/', $email))
            return true;
        return false;
    }

    /**
     * Generate a JWT from the login information.
     * TODO
     */
    private function _generateToken($id, $username){

        //Using pulic and private Rsa keys. I'm pretty sure this is faulting because an ssl license is not installed.
        //$signer = new Lcobucci\JWT\Signer\Rsa\Sha256();
        //$key = new Lcobucci\JWT\Signer\Key($this->config->item('pcron')['jwt_private_key']);

        //Using simple key for testing
        $signer = new Lcobucci\JWT\Signer\Hmac\Sha256();
        $key = new Lcobucci\JWT\Signer\Key('simpleKey');

        $now  = time();

        return (new Lcobucci\JWT\Builder())
            ->issuedBy($this->config->item('base_url'))
            ->permittedFor($this->config->item('base_url'))
            ->identifiedBy('pchron', true)
            ->issuedAt($now)
            ->expiresAt($now + 3600)
            ->withClaim('username', $username)
            ->withClaim('id', $id)
            ->getToken($signer, $key);
    }
}