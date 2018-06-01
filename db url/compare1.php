function sendPushNotification($strDeviceToken,$arrBody)
{
    global $pushFail;
    $token = $strDeviceToken;
    $pass = 'pushchat';
    $message = 'คุณพิสุทธิ์ กำลังไปเขาใหญ่กับฉัน แกอยากได้อะไรไหมกั๊ง (สายน้ำผึ้ง)pushnotification';
    
    
    $ctx = stream_context_create();
    stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck.pem');
    stream_context_set_option($ctx, 'ssl', 'passphrase', $pass);
    
    
    if(!$pushFail)
    {
        $fp = stream_socket_client(
                                   'ssl://gateway.sandbox.push.apple.com:2195', $err,
                                   $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
    }
    
    
    if (!$fp)
    {
        $pushFail = true;
        $error = "ติดต่อ Server ไม่ได้ ให้ลองย้อนกลับไป สร้าง pem ใหม่: $err $errstr" . PHP_EOL;
        writeToLog($error);
        
        return;
    }
    
    
    $body['aps'] = $arrBody;
    $json = json_encode($body);
    $msg = chr(0).pack('n', 32).pack('H*',$token).pack('n',strlen($json)).$json;
    $result = fwrite($fp, $msg, strlen($msg));
    if (!$result)
    {
        $status = "0";
        writeToLog("push notification: fail, device token : " . $strDeviceToken . ", payload: " . json_encode($arrBody));
    }
    else
    {
        $status = "1";
        writeToLog("push notification: success, device token : " . $strDeviceToken . ", payload: " . json_encode($arrBody));
    }
    
    fclose($fp);
    return $status;
}
