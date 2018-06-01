<?php
    require './../phpmailermaster/PHPMailerAutoload.php';
    include_once('./dbConnect.php');
//    setConnectionValue($_POST['dbName']);
    setConnectionValue('MINIMALIST_TEST');
    writeToLog("file: " . basename(__FILE__));
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    
    

if (isset ($_POST["toAddress"]) && isset ($_POST["subject"]) && isset ($_POST["body"])){
    $toAddress = $_POST["toAddress"];
    $subject = $_POST["subject"];
    $body = $_POST["body"];
} else {
    $toAddress = "jinglejill@hotmail.com";
    $subject = "-";
    $body = "-";
}

$mail = new PHPMailer;

//$mail->SMTPDebug = 3;                               // Enable verbose debug output

$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = 'cpanel02mh.bkk1.cloud.z.com';  // Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication // if not need put false
$mail->Username = 'admin@jinglejill.com';                 // SMTP username
$mail->Password = 'Jin1210!88';                           // SMTP password

$mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted // if nedd
$mail->Port = 465;                                    // TCP port to connect to // if nedd

$mail->From = 'admin@jinglejill.com'; // mail form user mail auth smtp
$mail->FromName = 'MINIMALIST_TEST';//$_POST['dbName'];
$mail->addAddress($toAddress); // Add a recipient
//$mail->addAddress('ellen@example.com'); // if nedd
//$mail->addReplyTo('info@example.com', 'Information'); // if nedd
//$mail->addCC('cc@example.com'); // if nedd
//$mail->addBCC('bcc@example.com'); // if nedd

$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments // if nedd
//$mail->addAttachment('http://minimalist.co.th/imageupload/34664/minimalistLogoReceipt.gif', 'logo.gif');    // Optional name // if nedd
$mail->AddEmbeddedImage('minimalistLogoReceipt.jpg', 'logo', 'minimalistLogoReceipt.jpg');
$mail->isHTML(true);                                  // Set email format to HTML // if format mail html // if no put false

$mail->Subject = $subject; // text subject
$mail->Body    = $body; // body

//$mail->AltBody = 'This is the body in plain text for non-HTML mail clients'; // if nedd

if(!$mail->send()){ // check send mail true/false
    echo 'Message could not be sent.'; // message if send mail not complete
    echo 'Mailer Error: ' . $mail->ErrorInfo; // message error
    $response = array('status' => 'Mailer Error: ' . $mail->ErrorInfo);
    
    $error = "send email fail, Mailer Error: " . $mail->ErrorInfo . ", modified user: " . $user;
    writeToLog($error);

    
    
    
    {
        //--------- ใช้สำหรับกรณี หน้าที่เรียกใช้ homemodel back ออกจากหน้าตัวเองไปแล้ว
        // Set autocommit to on
        mysqli_autocommit($con,TRUE);
        writeToLog("set auto commit to on");
        
        
        //alert query fail-> please check recent transactions again
        $type = 'alert';
        $action = '';
        
        
        
        $deviceToken = getDeviceTokenFromUsername($user);
        $sql = "insert into pushSync (DeviceToken, TableName, Action, Data, TimeSync) values ('$deviceToken','$type','$action','',now())";
        $res = mysqli_query($con,$sql);
        if(!$res)
        {
            $error = "query fail, sql: " . $sql . ", modified user: " . $user . " error: " . mysqli_error($con);
            writeToLog($error);
        }
        else
        {
            writeToLog("query success, sql: " . $sql . ", modified user: " . $_POST["modifiedUser"]);
            
            
            $pushSyncID = mysqli_insert_id($con);
            mysqli_close($con);
            
            writeToLog('pushsyncid: '.$pushSyncID);
            $paramBody = array(
                               'badge' => 0
//                               'type' => 'alert',
//                               'pushSyncID' => $pushSyncID
                               );
            sendPushNotification($deviceToken, $paramBody);
            
            ///----
        }
    }
}
else
{
//    echo 'Message has been sent'; // message if send mail complete
    $response = array('status' => '1');
}
    
    echo json_encode($response);
    exit();

?>
