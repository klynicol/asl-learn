<?php


/**
 * This class is extended from PHPMailer intented to provide quick functionality
 * for sending emails from any project on this server. 
 *  * Any exceptions will be logged into the php_mailer folder exceptions.txt
 *  * Automatically validates email addresses.
 *  * Property `easeMessage` is intended to store errors from the ease methods.
 * 
 * @uses php.mailer.factory.php
 * @author Mark Wickline 9/20/19
 */
use PHPMailer\PHPMailer;

require 'php_mailer/Exception.php';
require 'php_mailer/PHPMailer.php';
require 'php_mailer/SMTP.php';
/**
 * PHPMailerFactory
 *
 * Intended to be a single location for email functionality  all projects.
 * PHPMailer has been extended by php.mailer.ease.php
 *
 * Use example:
 *
//Use Example # 1
include 'php.mailer.factory.php';
$mail = PHPMailerFactory::create();

try {
    //Recipients
    $mail->From = "markgw@crystal-d.com"                  // From address
    $mail->FromName = "Mark Wickline"                     // From Name
    $mail->addAddress('joe@example.net', 'Joe User');     // Add a recipient
    $mail->addAddress('ellen@example.com');               // Name is optional
    $mail->addReplyTo('info@example.com', 'Information');
    $mail->addCC('cc@example.com');
    $mail->addBCC('bcc@example.com');

    // Attachments
    $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
    $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

    // Content
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = 'Here is the subject';
    $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    $mail->send();
    echo 'Message has been sent';

    // Clears
    $mail->clearCCs();
    $mail->clearBCCs();
    $mail->clearAddresses();
    $mail->clearAllRecipients();
    $mail->clearAttachments();
    $mail->clearReplyTos();
    $mail->clearCustomHeaders();

} catch (PHPMailer\Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}

Use Example # 2
//For quick functionality I've added a few methods @see php.mailer.ease.php, for those methods.

include 'php.mailer.factory.php';
$mail = PHPMailerFactory::create();

$mail->setFromEase('markgw@crystal-d.com', 'Mark Wickline');
$mail->setContentEase('This is subject', 'This is body');
if(!$mail->sendToEase([
    'touay@crystal-d.com,Toua Yang',
    'leey@crystal-d.com,Lee Yang'
])){
    echo $mail->easeMessage;
};
 *
 * @see https://phpmailer.github.io/PHPMailer/classes/PHPMailer.PHPMailer.PHPMailer.html
 * @author Mark Wickline 9/20/19
 */

class PHPMailerFactory
{
    /**
     * Creation method
     *
     * @param bool $exceptions Enables the use of exceptions.
     * @param bool $dev Enable verbose debug output.
     */
    public static function create($exceptions = false, $dev = false)
    {
        $mail = new PHPMailerEase($exceptions);
        $mail->IsSMTP();
        //$mail->SMTPSecure = 'tls'; // ssl is depracated
        //$mail->SMTPAuth = true; // Enable SMTP authentication
        $mail->SMTPAuth = false; // Enable SMTP authentication
        $smtpDebug = $dev ? 2 : 0;
        $mail->SMTPDebug = $smtpDebug; // 0 = off (for production use) - 1 = client messages - 2 = client and server messages
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ),
        );
        $mail->isHTML(true);
        $mail->CharSet = "UTF-8";
        $mail->Host = 'https://webmail.asl-learn.com';
        //$mail->Port = 587; // TLS only
        $mail->Port = 25; // TLS only
        //$mail->Username = 'icenine\phpmail';
        //$mail->Password = '2HV1X_Xsb7';
        return $mail;
    }
}


class PHPMailerEase extends PHPMailer\PHPMailer{
  
  
    /**
     * The message created by exceptions from this class.
     * @var string
     */
    public $easeMessage = '';

    public function __construct($exceptions){
        parent::__construct($exceptions);
    }

    /**
     * Sets the setFrom() and addReplyTo() methods
     * 
     * @param string $fromAddress
     * @param string $fromName
     */
    public function setFromEase($fromAddress, $fromName = ''){
        $fromAddress = trim($fromAddress);
        $fromName = trim($fromName);
        if(!$this->validateAddress($fromAddress, 'auto'))
            $this->easeMessage = "From address {$fromAddress} is not valid";
        $this->setFrom($fromAddress, $fromName);
        $this->addReplyTo($fromAddress, $fromName);
    }


    /**
     * Sets the subject and body of the email. Also strips the HTML tags and sets an
     * AltBody.
     * 
     * @param string $subject
     * @param string $body
     */
    public function setContentEase($subject, $body){
        $this->Subject = $subject;
        $this->Body    = $body;
        $this->AltBody = strip_tags($body);
    }

    /**
     * Set's email recipients. Sends the email and clears the email object for 
     * the next email to be sent.
     * 
     * This method will authomatically do a try{}catch{} and log exceptions in the php/php_mailer
     * folder.
     * 
     * @param array $to A comma delimited list of adddress,name (E.G. ["markgw@crystal-d.com,Mark Wickline"])
     * @param bool $clear Should all the email content be cleared?
     * @return bool Success
     */
    public function sendToEase($to, $clear = true){
        $result = false;
        //If error message exists don't send any emails
        if($this->easeMessage)
            return false;
        foreach($to as $recipient){
            if(!$recipient || gettype($recipient) !== 'string'){
                $this->easeMessage = 'A recipient cannot be empty';
                return false;
            }
            $split = explode(',', $recipient);
            $address = trim($split[0]);
            if(!$this->validateAddress($address, 'auto')){
                $this->easeMessage = "Recipient address {$address} is not valid";
                return false;
            }
            $name = trim($split[1] ?? '');
            $this->addAddress( $address, $name);
        }
        try{
            $result = $this->send();
        } catch (PHPMailer\Exception $e) {
            $this->easeMessage = $this->ErrorInfo;
            $this->_log($this->ErrorInfo);
        }
        if($clear){
            $this->clearCCs();
            $this->clearBCCs();
            $this->clearAddresses();
            $this->clearAllRecipients();
            $this->clearAttachments();
            $this->clearReplyTos();
        }
        return $result;
    }
    
    /**
     * Clears the email's contents.
     *
     * Added by Mark Willcox 11/27/2019
     * 
     * @param none
     * @return bool true
     */
    public function clear(){
        $this->clearCCs();
        $this->clearBCCs();
        $this->clearAddresses();
        $this->clearAllRecipients();
        $this->clearAttachments();
        $this->clearReplyTos();
        $this->clearCustomHeaders();
    }
     
    /**
     * Logs exceptions in the php_mailer folder as exceptions.txt
     * 
     * @param string $input
     */
    private function _log($input){
        $fp = fopen('../logs/phpmailexceptions.txt', 'w');
        fwrite($fp, $input . PHP_EOL);
        fclose($fp);
    }
}
