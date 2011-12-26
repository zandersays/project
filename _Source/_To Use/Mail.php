<?php
class Mail {
    public static function send($fromName, $fromAddress, $toArray, $subject, $message) {
        include_once(SITE_PATH.'php/packages/phpmailer/class.phpmailer.php');

        $email = new PHPMailer();
        $email->From = $fromAddress;
        $email->FromName = $fromName;
        $email->Subject = $subject;
        $email->AltBody = $message;
        $email->MsgHTML(nl2br($message));

        if(is_array($toArray)) {
            foreach($toArray as $toAddress) {
                $email->AddAddress($toAddress);
            }
        }

        if($email->Send()) {
            $response = array('status' => 'success', 'response' => 'Message successfully sent.');
        }
        else {
            $response = array('status' => 'failure', 'response' => 'Mailer error: '.$email->ErrorInfo);
        }

        return $response;
    }
}
?>