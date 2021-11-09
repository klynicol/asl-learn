<?php defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'controllers/Base.php';

class Splash extends Base {
    public function __construct(){
        parent::__construct();
        $this->load->model('Base_Model', 'model');
    }

    public function joinMailingList_get(){
        $this->basicRateLimit();
        $parms = $this->get(null, true);
        $this->model->ins('t_guest_mail_list', ['email' => $parms['email']]);
        $this->response([
            'status' => true
        ]);
    }


    public function contactUsForm_post(){
        $this->basicRateLimit();

        $parms = $this->post(null, true);
        include APPPATH . 'libraries/PHP_Mailer.php';
        $mail = PHPMailerFactory::create();

        $body = "<h4>New 'Contact Us' form submission from asl-learn.com</h4>";
        $body .= "Name: {$parms['name']}<br>";
        $body .= "Email: {$parms['email']}<br><br>";
        $body .= "Message:<br>";
        $body .= $parms['message'];

        $mail->setFromEase('noreply@asl-learn.com', 'No Reply');
        $mail->setContentEase('asl-learn.com Contact Form', $body);
        if(!$mail->sendToEase([
            'info@asl-learn.com, ASL Info'
        ])){
            $this->response([
                'status' => false,
                'msg' => "Email did not send, please contact admin@asl-learn.com"
            ]);
        };
        $this->response(['status' => true]);
    }

    /**
     * Get the guest list.
     */
    public function getGuestMailList_get(){
        parent::_init();
        $this->basicRateLimit();
        $this->verifyAndLoadUser();

        $parms = $this->get(null, true);
        $this->response([
            'status' => true,
            'message' => 'list returned',
            'data' => $this->model->getAll('t_guest_mail_list')
        ]);
    }

    // public function test_get(){
    //     $this->response([
    //         'status' => true,
    //         'msg' => 'here'
    //     ]);
    // }
}