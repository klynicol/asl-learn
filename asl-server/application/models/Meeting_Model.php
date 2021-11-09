<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * @author Mark Wickline 08/04/2021
 */
class Meeting_Model extends Base_Model{
    public function __construct(){
        parent::__construct();
    }

    public function insGuestReg($parms){
        $this->encryptedColumns = [
            't_meeting_guest_regs' => [
                'first_name' => ['bi' => true],
                'last_name' => ['bi' => true],
                'email' => ['bi' => true],
            ]
        ];
        $parms = $this->encryptRowAuto('t_meeting_guest_regs', $parms);
        return $this->ins('t_meeting_guest_regs', $parms);
    }

    public function getUserMeetings($userID){
        if(!$meetings = $this->getWhere('t_meetings', ['user_id' => $userID])){
            return false;
        }
        foreach($meetings as &$meeting){
            $guestRegSelects = "id, first_name, last_name, email, message, create_date, is_paid, amount_paid";
            $guestRegs = $this->getWhere(
                't_meeting_guest_regs', ['meeting_id' => $meeting['id']], $guestRegSelects);
            $guestRegDecryptCols = ['first_name', 'last_name', 'email'];
            foreach($guestRegs as &$guestReg){
                $guestReg = $this->decryptRowByKeys(
                    't_meeting_guest_regs', $guestReg, $guestRegDecryptCols);
            }
            unset($guestReg);
            $meeting['guest_regs'] = $guestRegs;
            $meeting['user_regs'] = [];
        }
        unset($meeting);
        return $meetings;
    }

    public function getAvailableMeetings(){
        $sql = "SELECT
            m.id,
            m.description,
            m.date_time,
            m.title,
            m.description,
            m.cost,
            u.first_name,
            u.last_name
            FROM t_meetings m
            LEFT JOIN t_users u
            ON m.user_id = u.id";
        $results = $this->query($sql);
        foreach($results as &$res){
            $res = $this->decryptRowByKeys('t_users', $res, ['first_name', 'last_name']);
        }
        unset($res);
        return $results;
    }
}