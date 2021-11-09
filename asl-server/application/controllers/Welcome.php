<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Quick and dirty controller... Delete this when going full production.
 * 
 * @author Mark Wickline 2021-07-10
 */

class Welcome extends CI_Controller{
	public function __construct(){
		parent::__construct();
        $this->load->helper('form');
	}

    public function yyyy(){
        //Quick and dirty for to hit the register endpoint.
        $this->load->view('yyyy');
    }

    public function zzzz(){
        //Quick and dirty reset password.
        $this->load->view('zzzz');
    }

    /**
     * Link sent from password reset will redirect to here.
     * We need to verify their hash 
     */
    public function ooof(){
        $this->load->view('oof');
    }
}