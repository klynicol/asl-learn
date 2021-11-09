<?php defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'controllers/Base.php';

class Meeting extends Base {
    public function __construct(){
        parent::__construct();
        parent::_init();
        $this->load->model('Meeting_Model', 'model');
    }

    public function guestSignup_post(){
        $this->basicRateLimit();
        $parms = $this->post(null, true);
        $parms['is_paid'] = 1;
        $this->model->insGuestReg($parms);
        // Email recipient with information
        include APPPATH . 'libraries/PHP_Mailer.php';
        $mail = PHPMailerFactory::create();

        $body = "Thank you for signing up";

        $mail->setFromEase('noreply@asl-learn.com', 'No Reply');
        $mail->setContentEase('asl-learn.com Thank you for registering', $body);
        if(!$mail->sendToEase([
            "{$parms['email']}, {$parms['first_name']} {$parms['last_name']}"
        ])){
            $this->response([
                'status' => false,
                'msg' => "Email did not send, please contact admin@asl-learn.com"
            ]);
        };
        //TODO TODO TODO TODO TODO TODO TODO
        //TODO
        // Send email to meeting creator informing about registration
        //TODO TODO TODO TODO
        // $body = "Thank you for signing up";

        // $mail->setFromEase('noreply@asl-learn.com', 'No Reply');
        // $mail->setContentEase('asl-learn.com Thank you for registering', $body);
        // if(!$mail->sendToEase([
        //     "{$parms['email']}, {$parms['first_name']} {$parms['last_name']}"
        // ])){
        //     $this->response([
        //         'status' => false,
        //         'msg' => "Email did not send, please contact admin@asl-learn.com"
        //     ]);
        // };
        $this->response([
            'status' => true,
            'data' => [
                'meeting' => $parms
            ]
        ], 200);
    }

    public function guestGetAllMeetings_get(){
        $this->basicRateLimit();
        $this->response([
            'status' => true,
            'data' => [
                'meetings' => $this->model->getAvailableMeetings()
            ]
        ], 200);
    }

    public function createMeeting_post(){
        $this->basicRateLimit();
        $this->verifyAndLoadUser();
        $parms = $this->post(null, true);
        // The date_time from parms will be the user's timezone. let's convert it to local timezone.
        $userDateTime = new DateTime($parms['date_time'], new DateTimeZone('UTC'));
        $parms['date_time'] = $userDateTime->format("Y-m-d H:i:s");
        $parms['create_date'] = sqlTimeStamp();
        $parms['modify_date'] = sqlTimeStamp();
        $parms['user_id'] = $this->user_object->id;
        $id = $this->model->ins('t_meetings', $parms);
        $parms['id'] = $id;
        $parms['guest_regs'] = [];
        $this->response([
            'status' => true,
            'data' => [
                'meeting' => $parms
            ]
        ], 200);
    }

    public function loadUserMeetings_get(){
        $this->basicRateLimit();
        $this->verifyAndLoadUser();
        $this->response([
            'status' => true,
            'data' => $this->model->getUserMeetings($this->user_object->id)
        ], 200);
    }
    
    public function updateMeeting_post(){
        $this->basicRateLimit();
        $this->verifyAndLoadUser();
        $parms = $this->post(null, true);
        $parms['modify_date'] = sqlTimeStamp();
        $userDateTime = new DateTime($parms['date_time'], new DateTimeZone('UTC'));
        $parms['date_time'] = $userDateTime->format("Y-m-d H:i:s");
        $where = [
            'user_id' => $this->user_object->id,
            'id' => $parms['id']
        ];
        unset($parms['id']);
        $success = $this->model->updWhere('t_meetings', $where, $parms);
        $this->response([
            'status' => true,
            'data' => $success
        ], 200);
    }
    
    public function deleteMeeting_get(){
        $this->basicRateLimit();
        $this->verifyAndLoadUser();
        $id = $this->get('id', true);
        $where = [
            'user_id' => $this->user_object->id,
            'id' => $id
        ];
        $success = $this->model->delWhere('t_meetings', $where);
        $this->response([
            'status' => true,
            'data' => $success
        ], 200);
    }
}