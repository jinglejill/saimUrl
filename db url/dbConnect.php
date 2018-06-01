<?php
    
    
    //conection variable
    $con;
    $con2;
    $globalDBName;    

    function executeMultiQueryArray($sql)
    {
        global $con;
        if (mysqli_multi_query($con, $sql)) {
            $arrOfTableArray = array();
            $resultArray = array();
            do {
                /* store first result set */
                if ($result = mysqli_store_result($con)) {
                    while ($row = mysqli_fetch_object($result)) {
                        array_push($resultArray, $row);
                    }
                    array_push($arrOfTableArray,$resultArray);
                    $resultArray = [];
                    mysqli_free_result($result);
                }
                if(!mysqli_more_results($con))
                {
                    break;
                }
            } while (mysqli_next_result($con));
            
            return $arrOfTableArray;
        }
        return "";
    }
    
    function printAllPost()
    {
        global $con;
        $paramAndValue;
        $i = 0;
        foreach ($_POST as $param_name => $param_val)
        {
            if($i == 0)
            {
                $paramAndValue = "Param=Value: ";
            }
            $paramAndValue .= "$param_name=$param_val&";
            $_POST['$param_name'] = mysqli_real_escape_string($con,$param_val);
            $i++;
        }
        
        if(sizeof($_POST) > 0)
        {
            writeToLog($paramAndValue);
        }
    }
    
    function putAlertToDevice($user)
    {
        global $con;
        // push alert to device
        // Set autocommit to on
        mysqli_autocommit($con,TRUE);
        writeToLog("set auto commit to on");
        
        
        //alert query fail-> please check recent transactions again
        $type = 'alert';
        $action = '';
        writeToLog("fail from push notification: " . $type);
        
        
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
            writeToLog("query success, sql: " . $sql . ", modified user: " . $user);
            
            $pushSyncID = mysqli_insert_id($con);
            writeToLog('pushsyncid: '.$pushSyncID);
            $paramBody = array(
                               'badge' => 0
                               );
            sendPushNotification($deviceToken, $paramBody);
            //----------
        }
        mysqli_close($con);
    }
    function setConnectionValue($dbName)
    {
        global $con;
        global $con2;
        global $globalDBName;
        
        
        if($dbName == 'dbName')
        {
            $dbName = 'MINIMALIST_TEST';
        }
        $globalDBName = $dbName;
        
        
        // Create connection
        $con=mysqli_connect("localhost",$dbName,"123456",$dbName);
        $con2=mysqli_connect("localhost",$dbName,"123456",$dbName);
        
        
        $timeZone = mysqli_query($con,"SET SESSION time_zone = '+07:00'");
        mysqli_set_charset($con, "utf8");
    }
    
    function getDeviceTokenFromUsername($user)
    {
        global $con;
        $sql = "select DeviceToken from useraccount where username = '$user'";
        $selectedRow = getSelectedRow($sql);
        $deviceToken = $selectedRow[0]['DeviceToken'];
        
        
        writeToLog('getDeviceTokenFromUsername deviceToken: ' . $deviceToken);
        return $deviceToken;
    }
    function doQueryTask($con,$sql,$user)
    {
        $res = mysqli_query($con,$sql);        
        if(!$res)
        {
            $error = "query fail, sql: " . $sql . ", modified user: " . $user . " error: " . mysqli_error($con);
            writeToLog($error);
            
            
            // Rollback transaction
            mysqli_rollback($con);
            $response = array('status' => $error);
            return $response;
        }
        else
        {
            writeToLog("query success, sql: " . $sql . ", modified user: " . $_POST["modifiedUser"]);
        }
        return "";
    }
    function doQueryTask2($con,$sql,$user)
    {
        $res = mysqli_query($con,$sql);
        if(!$res)
        {
            $error = "query fail, sql: " . $sql . ", modified user: " . $user . " error: " . mysqli_error($con);
            writeToLog($error);
            
    
            $response = array('status' => $error);
            return $response;
        }
        else
        {
            writeToLog("query success, sql: " . $sql . ", modified user: " . $_POST["modifiedUser"]);
        }
        return "";
    }

    function doPushNotificationTaskToDevice($con,$user,$deviceToken,$selectedRow,$type,$action)
    {
        $sql = "insert into pushSync (DeviceToken, TableName, Action, Data, TimeSync) values ('$deviceToken','$type','$action','" . json_encode($selectedRow, JSON_UNESCAPED_UNICODE) . "',now())";
        $ret = doQueryTask2($con,$sql,$_POST["modifiedUser"]);
        if($ret != "")
        {
            mysqli_rollback($con);
            return $ret;
        }
        $pushSyncID = mysqli_insert_id($con);
        writeToLog('pushsyncid: '.$pushSyncID);
        
        return "";
    }
    
    function doPushNotificationTask($con,$user,$deviceToken,$selectedRow,$type,$action)
    {
        $pushDeviceTokenList = getDeviceTokenList($deviceToken);
        
        foreach ($pushDeviceTokenList as $iDeviceToken)
        {
            //query statement
            if(strcmp($type,"sProductSales") == 0)
            {
                $sql = "insert into pushSync (DeviceToken, TableName, Action, Data, TimeSync) values ('$iDeviceToken','$type','$action','" . $selectedRow . "',now())";
            }
            else if(strcmp($type,"sCompareInventory") == 0)
            {
                $sql = "insert into pushSync (DeviceToken, TableName, Action, Data, TimeSync) values ('$iDeviceToken','$type','$action','" . $selectedRow . "',now())";
            }
            else
            {
                $sql = "insert into pushSync (DeviceToken, TableName, Action, Data, TimeSync) values ('$iDeviceToken','$type','$action','" . json_encode($selectedRow, JSON_UNESCAPED_UNICODE) . "',now())";
            }
            $ret = doQueryTask2($con,$sql,$_POST["modifiedUser"]);
            if($ret != "")
            {
                mysqli_rollback($con);
                return $ret;
            }
            $pushSyncID = mysqli_insert_id($con);
            writeToLog('pushsyncid: '.$pushSyncID);
        }
        return "";
    }
    
    function doPushNotificationTaskAsLog($con,$user,$deviceToken,$selectedRow,$type,$action)
    {
        //query statement
        $sql = "insert into pushSync (DeviceToken, TableName, Action, Data, TimeSync,TimeSynced) values ('$deviceToken','$type','delete log','" . json_encode($selectedRow, true) . "',now(),now())";
        $ret = doQueryTask2($con,$sql,$_POST["modifiedUser"]);
        if($ret != "")
        {
            mysqli_rollback($con);
            return $ret;
        }
        $pushSyncID = mysqli_insert_id($con);
        writeToLog('delete log pushsyncid: '.$pushSyncID);
        return "";
    }
    
    function sendPushNotificationToAllDevices()
    {
        $pushDeviceTokenList = getAllDeviceTokenList();
        
        foreach ($pushDeviceTokenList as $iDeviceToken)
        {
            sendPushNotificationToDevice($iDeviceToken);
        }
    }
    
    function sendPushNotificationToOtherDevices($deviceToken)
    {
        $pushDeviceTokenList = getDeviceTokenList($deviceToken);
        foreach ($pushDeviceTokenList as $iDeviceToken)
        {
            sendPushNotificationToDevice($iDeviceToken);
        }
    }
    
    function sendPushNotificationToDevice($deviceToken)
    {
        $paramBody = array(
                           'badge' => 0
                           );
        sendPushNotification($deviceToken, $paramBody);
    }
    
    function doApplePushNotificationTask($con,$user,$deviceToken,$badge)
    {
        $deviceTokenAndCountNotSeenList = getDeviceTokenAndCountNotSeenList($user,$deviceToken);
        foreach ($deviceTokenAndCountNotSeenList as $deviceTokenAndCountNotSeen)
        {
            $deviceTokenCountNotSeen = $deviceTokenAndCountNotSeen["DeviceToken"];
            $countNotSeen = $deviceTokenAndCountNotSeen["CountNotSeen"];
            $username = $deviceTokenAndCountNotSeen["Username"];
            writeToLog('device token: ' . $deviceToken. ', count not seen: ' . $countNotSeen);
            $updateBadge = $badge+$countNotSeen;
            

            //query statement
            $sql = "update useraccount set countnotseen = '$updateBadge' where username = '$username'";
            $res = mysqli_query($con,$sql);
            if(!$res)
            {
                $error = "query fail, sql: " . $sql . ", modified user: " . $user . " error: " . mysqli_error($con);
                writeToLog($error);
                
                
                $response = array('status' => $error);
                return $response;
            }
            else
            {
                writeToLog("query success, sql: " . $sql . ", modified user: " . $_POST["modifiedUser"]);
            }
 
            $paramBody = array(
                               'badge' => $updateBadge
                               );
            sendApplePushNotification($deviceTokenCountNotSeen, $paramBody);
        }
        return "";
    }
    
    function updateCountNotSeen($con,$user,$deviceToken,$badge)
    {
        $deviceTokenAndCountNotSeenList = getDeviceTokenAndCountNotSeenList($user,$deviceToken);
        foreach ($deviceTokenAndCountNotSeenList as $deviceTokenAndCountNotSeen)
        {
            $deviceTokenCountNotSeen = $deviceTokenAndCountNotSeen["DeviceToken"];
            $countNotSeen = $deviceTokenAndCountNotSeen["CountNotSeen"];
            $username = $deviceTokenAndCountNotSeen["Username"];
            writeToLog('device token: ' . $deviceToken. ', count not seen: ' . $countNotSeen);
            writeToLog('badge to add: ' . $badge);
            $updateBadge = $badge+$countNotSeen;
            
            
            //query statement
            $sql = "update useraccount set countnotseen = $updateBadge where username = '$username'";
            $ret = doQueryTask2($con,$sql,$_POST["modifiedUser"]);
            if($ret != "")
            {
//                mysqli_rollback($con);
                return $ret;
            }
            
            $paramBody = array(
                               'badge' => $updateBadge
                               );
            sendPushNotification($deviceTokenCountNotSeen, $paramBody);
        }
        return "";
    }
    
    function getSelectedRow($sql)
    {
        global $con;        
        if ($result = mysqli_query($con, $sql))
        {
            $resultArray = array();
            $tempArray = array();
            
            while($row = mysqli_fetch_array($result))
            {
                $tempArray = $row;
                array_push($resultArray, $tempArray);
            }
            mysqli_free_result($result);
        }
        if(sizeof($resultArray) == 0)
        {
            $error = "query: selected row count = 0, sql: " . $sql . ", modified user: " . $_POST["modifiedUser"];
            writeToLog($error);
        }
        else
        {
            writeToLog("query success, sql: " . $sql . ", modified user: " . $_POST["modifiedUser"]);
        }
        
        return $resultArray;
    }
    
    function getAllDeviceTokenList()
    {
        global $con;
        $sql = "select DeviceToken from Device where DeviceToken != ''";
        if ($result = mysqli_query($con, $sql))
        {
            $deviceTokenList = array();
            while($row = mysqli_fetch_array($result))
            {
                $strDeviceToken = $row["DeviceToken"];
                array_push($deviceTokenList, $strDeviceToken);
            }
            mysqli_free_result($result);
        }
        return $deviceTokenList;
    }
    
    function getDeviceTokenList($modifiedDeviceToken)
    {
        global $con;
        $sql = "select DeviceToken from Device where DeviceToken != '' and DeviceToken != '" . $modifiedDeviceToken . "'";
        if ($result = mysqli_query($con, $sql))
        {
            $deviceTokenList = array();
            while($row = mysqli_fetch_array($result))
            {
                $strDeviceToken = $row["DeviceToken"];
                array_push($deviceTokenList, $strDeviceToken);
            }
            mysqli_free_result($result);
        }

        return $deviceTokenList;
    }
    
    function getDeviceTokenAndCountNotSeenList($modifiedUser,$modifiedDeviceToken)
    {
        global $con;
        $sql = "select Device.DeviceToken, UserAccount.CountNotSeen, UserAccount.Username from Device left join UserAccount on Device.DeviceToken = UserAccount.DeviceToken where Device.DeviceToken != '" . $modifiedDeviceToken . "' and Device.DeviceToken != '' and UserAccount.PushOnSale = 1";
        writeToLog("countNotSeenList: " . $sql);
        if ($result = mysqli_query($con, $sql))
        {
            $deviceTokenAndCountNotSeenList = array();
            while($row = mysqli_fetch_array($result))
            {
                $strDeviceToken = $row["DeviceToken"];
                $strCountNotSeen = $row["CountNotSeen"];
                $strUsername = $row["Username"];
                array_push($deviceTokenAndCountNotSeenList, array("DeviceToken" => $strDeviceToken,"CountNotSeen" => $strCountNotSeen,"Username"=>$strUsername));
            }
            mysqli_free_result($result);
        }
        return $deviceTokenAndCountNotSeenList;
    }
    
    function writeToLogFromParentFolder($message)
    {
        global $globalDBName;
        $mday = getdate()[mday];
        $day = sprintf("%02d", $mday);
        $mon = getdate()[mon];
        $month = sprintf("%02d", $mon);
        $year = getdate()[year];
        $logPath = './' . $globalDBName . '/TransactionLog/';
        $logFile = 'saimTransactinLog' . $year . $month . $day . '.log';
        if (!file_exists($logPath)) {
            mkdir($logPath, 0777, true);
        }
        $logPath = $logPath . $logFile;
        
        
        if ($fp = fopen($logPath, 'at'))
        {
            fwrite($fp, date('c') . ' ' . $message . PHP_EOL);
            fclose($fp);
        }
    }
    
    function writeToLog($message)
    {
        global $globalDBName;
        $mday = getdate()[mday];
        $day = sprintf("%02d", $mday);
        $mon = getdate()[mon];
        $month = sprintf("%02d", $mon);
        $year = getdate()[year];
        
        $fileName = 'saimTransactinLog' . $year . $month . $day . '.log';
        $filePath = $_SERVER['DOCUMENT_ROOT'] . '/SAIM/' . $globalDBName . '/TransactionLog/';
        if (!file_exists($filePath))
        {
            mkdir($filePath, 0777, true);
        }
        
        $filePath = $filePath . $fileName;
        
        
        if ($fp = fopen($filePath, 'at'))
        {
            $arrMessage = explode("\\n",$message);
            if(sizeof($arrMessage) > 1)
            {
                foreach($arrMessage as $eachLine)
                {
                    $newMessge .= PHP_EOL . $eachLine ;
                }
            }
            else
            {
                $newMessge = $message;
            }
            
            fwrite($fp, date('c') . ' ' . $newMessge . PHP_EOL);
            fclose($fp);
        }
    }

    function writeToErrorLog($message)
    {
        global $globalDBName;
        $mday = getdate()[mday];
        $day = sprintf("%02d", $mday);
        $mon = getdate()[mon];
        $month = sprintf("%02d", $mon);
        $year = getdate()[year];
        
        $fileName = 'saimErrorLog' . $year . $month . $day . '.log';
        $filePath = $_SERVER['DOCUMENT_ROOT'] . '/SAIM/' . $globalDBName . '/TransactionLog/';
        if (!file_exists($filePath))
        {
            mkdir($filePath, 0777, true);
        }
        
        $filePath = $filePath . $fileName;
        
        
        if ($fp = fopen($filePath, 'at'))
        {
            $arrMessage = explode("\\n",$message);
            if(sizeof($arrMessage) > 1)
            {
                foreach($arrMessage as $eachLine)
                {
                    $newMessge .= PHP_EOL . $eachLine ;
                }
            }
            else
            {
                $newMessge = $message;
            }
            
            fwrite($fp, date('c') . ' ' . $newMessge . PHP_EOL);
            fclose($fp);
        }
    }
    
    function sendPushNotification($strDeviceToken,$arrBody)
    {
        writeToLog("send push to device: " . $strDeviceToken . ", body: " . json_encode($arrBody));
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
    function sendApplePushNotification($strDeviceToken,$arrBody)
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
            $error = "apple push: ติดต่อ Server ไม่ได้ ให้ลองย้อนกลับไป สร้าง pem ใหม่: $err $errstr" . PHP_EOL;
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
            writeToLog("apple push notification: fail, device token : " . $strDeviceToken . ", payload: " . json_encode($arrBody));
        }
        else
        {
            $status = "1";
            writeToLog("apple push notification: success, device token : " . $strDeviceToken . ", payload: " . json_encode($arrBody));
        }
        
        fclose($fp);
        return $status;
    }
    function sendTestApplePushNotification($strDeviceToken,$arrBody)
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
            $error = "apple push: ติดต่อ Server ไม่ได้ ให้ลองย้อนกลับไป สร้าง pem ใหม่: $err $errstr" . PHP_EOL;
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
            writeToLog("apple push notification: fail, device token : " . $strDeviceToken . ", payload: " . json_encode($arrBody));
        }
        else
        {
            $status = "1";
            writeToLog("apple push notification: success, device token : " . $strDeviceToken . ", payload: " . json_encode($arrBody));
        }
        
        fclose($fp);
        return $status;
    }

?>
