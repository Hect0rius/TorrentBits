<?php
require 'Mail/Exception.php';
require 'Mail/PHPMailer.php';
require 'Mail/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmail($to, $Subject, $Msg, $From) {

    global $TBDEV;
    $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
try {
    //Server settings
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = $TBDEV['mail_host'];  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = $TBDEV['mail_user'];                 // SMTP username
    $mail->Password = $TBDEV['mail_pwd'];                           // SMTP password
    $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = $TBDEV['mail_port'];                                    // TCP port to connect to

    if(substr($to, 0, 1) == "'" && substr($to, strlen($to) - 2, 1) == "'") {
        $to = substr($to, 1, strlen($to) - 2);
    }
    //Recipients
    $mail->setFrom($From);
    $mail->addAddress($to);     // Add a recipient
    //Content
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = $Subject;
    $mail->AltBody = $Msg;
    $mail->Body = $Msg;
    $mail->send();
    return true;
} catch (Exception $e) {
    __LOG("User '{$Username}' recieved the mail error : " . $e);
}
return false;
}