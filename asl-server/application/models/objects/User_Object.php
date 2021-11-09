<?php defined('BASEPATH') OR exit('No direct script access allowed');
include_once APPPATH . 'models/Data_Model.php';
/**
 * User Object model.
 * 
 * @author Mark Wickline 2020-03-03
 */

class User_Object extends Data_Model{

    public $id;
    public $username = "";
    public $first_name = "";
	public $last_name = "";
	public $email = "";
	public $create_date;
	public $last_login_date;
    public $last_activity_date;
    public $app_state;
	public $user_type = 0;
    public $pass_hash;
    public $api_token;

    public function __construct(){
        parent::__construct();
        //Help describe $this to Data_Model
        $this->table = 't_users';
        $ignoreFields = [
            'app_state',
            'user_type',
            // 'pass_hash'
        ];
        $this->ignoreFields = array_merge($this->ignoreFields, $ignoreFields);

        $txtFields = [
            'username' => [ 'length' => 255],
            'first_name' => [ 'length' => 255],
            'last_name' => [ 'length' => 255],
            // https://stackoverflow.com/questions/386294/what-is-the-maximum-length-of-a-valid-email-address
            'email' => [ 'length' => 320]
        ];
        $this->txtFields = array_merge($this->txtFields, $txtFields);

        $encryptedFields = [
            'first_name' => [ 'blind_index' => 'first_name_bli' ],
            'last_name' => [ 'blind_index' => 'last_name_bli' ],
            'email' => [ 'blind_index' => 'email_bli' ]
        ];
        $this->encryptedFields = array_merge($this->encryptedFields, $encryptedFields);

        $dateTimeFields = [
            'create_date',
            'last_login_date',
            'last_activity_date'
        ];
        $this->dateTimeFields = array_merge($this->dateTimeFields, $dateTimeFields);
    }

    public function loadFromUsername($username){
        $user = $this->getRowWhere($this->table, ['username' => $username]);
        if(empty($user)) return false;
        $this->loadThis($user);
        $this->stampActivity();
        return true;
    }

    public function loadFromEmail($email){
        $this->cipher->clear();
        $this->cipher->setTable('t_users');
        $blindIndex = $this->cipher->getBlindIndex($email, 'email', 'email_bli');
        $user = $this->getRowWhere($this->table, ['email_bli' => $blindIndex]);
        if(empty($user)){
            return false;
        }
        $this->loadThis($user);
        // Double check since cipher_sweet can currently overlap.
        if($this->email !== $email){
            return false;
        }
        $this->stampActivity();
        return true;
    }

    public function verifyPassword($password){
        if(password_verify($password, $this->pass_hash))
            return true;
        return false;
	}

	public function hashPassword($password){
		$this->pass_hash = password_hash($password, PASSWORD_DEFAULT);
	}

    public function getToken(){
        return $this->api_token;
    }

    /**
     * Whenever a user reloads or performs activities, lets stamp
     * their activity column. Wondering if this could be handled using JWT instead.
     */
    public function stampActivity(){
        if(empty($this->id)){
            return;
        }
        $this->last_activity_date = new DateTime();
        $this->updWhere($this->table, ['id' => $this->id], 
            ['last_activity_date' => $this->last_activity_date->format('Y-m-d H:i:s')]);
    }

    /**
     * Handle a login from a user.
     */
    public function login(){
        $this->generateToken();
        $this->last_login_date = new DateTime();
        $this->last_activity_date = new DateTime();
        $this->saveThis();
    }

    /**
     * Generate a reusable token after the user logs in. There's nothing special
     * about the token, just a random set of digits. This will be stored in the database
     * so user can access all their endpoints later.
     */
    public function generateToken(){
        $this->api_token = generateRandomString(25);
    }
}