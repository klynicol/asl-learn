<?php defined('BASEPATH') OR exit('No direct script access allowed');
include_once APPPATH . "/libraries/Cipher_Sweet.php";
/**
 * Basic model to stick methods that help interpret some basic information.
 * At some point if this file is getting to be we can split it up into
 * multiple helpers. Such as User_Helper_Model
 * .
 * 
 * @author Mark Wickline 2020-01-12
 */
class Helper_Model extends Base_Model{
    public function __construct(){
        parent::__construct();
    }

    public function userEmailExists($email){
        $this->cipher->clear();
        $this->cipher->setTable('t_users');
        $blindIndex = $this->cipher->getBlindIndex($email, 'email', 'email_bli');
        if($this->rowExists('t_users', ['email_bli' => $blindIndex])){
            return true;
        }
        return false;
    }
}