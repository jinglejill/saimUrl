<?php
require 'phpmailermaster/PHPMailerAutoload.php';

if (isset ($_POST["toAddress"]) && isset ($_POST["subject"]) && isset ($_POST["body"])){
    $toAddress = $_POST["toAddress"];
    $subject = $_POST["subject"];
    $body = $_POST["body"];
} else {
    $toAddress = "thidaporn.kijkamjai@gmail.com";
    $subject = "-";
    $body = "-";
}

$mail = new PHPMailer;

//$mail->SMTPDebug = 3;                               // Enable verbose debug output

$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = 'mail.jinglejill.com';  // Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication // if not need put false
$mail->Username = 'admin@jinglejill.com';                 // SMTP username
$mail->Password = 'Jin1210!';                           // SMTP password

//$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted // if nedd
//$mail->Port = 587;                                    // TCP port to connect to // if nedd

$mail->From = 'admin@jinglejill.com'; // mail form user mail auth smtp
$mail->FromName = 'Mailer';
$mail->addAddress($toAddress); // Add a recipient
//$mail->addAddress('ellen@example.com'); // if nedd
//$mail->addReplyTo('info@example.com', 'Information'); // if nedd
//$mail->addCC('cc@example.com'); // if nedd
//$mail->addBCC('bcc@example.com'); // if nedd

$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments // if nedd
//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name // if nedd
$mail->isHTML(true);                                  // Set email format to HTML // if format mail html // if no put false

$mail->Subject = $subject; // text subject
$mail->Body    = $body; // body

//$mail->AltBody = 'This is the body in plain text for non-HTML mail clients'; // if nedd

if(!$mail->send()){ // check send mail true/false
    echo 'Message could not be sent.'; // message if send mail not complete
    echo 'Mailer Error: ' . $mail->ErrorInfo; // message error
    $response = array('status' => 'Mailer Error: ' . $mail->ErrorInfo);
    
}else{
//    echo 'Message has been sent'; // message if send mail complete
    $response = array('status' => '1');
}
    echo json_encode($response);
    exit();

?>