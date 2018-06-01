<?php
    include_once('dbConnect.php');
    setConnectionValue("SAIM");
    writeToLogCredentials("file: " . basename(__FILE__));
    

    function writeToLogCredentials($message)
    {
        global $globalDBName;
        $mday = getdate()[mday];
        $day = sprintf("%02d", $mday);
        $mon = getdate()[mon];
        $month = sprintf("%02d", $mon);
        $year = getdate()[year];
        $path = './CredentialTransactionLog/';
        $file = 'saimTransactinLog' . $year . $month . $day . '.log';
        
        
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $path = $path . $file;
        
        
        
        if ($fp = fopen($path, 'at'))
        {
            fwrite($fp, date('c') . ' ' . $message . PHP_EOL);
            fclose($fp);
        }
    }
    
    function getSelectedRowCredentials($sql)
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
            $error = "query: selected row count = 0, sql: " . $sql . ", modified user: " . $username . ", device token: " . $deviceToken;
            writeToLogCredentials($error);
        }
        else
        {
            writeToLogCredentials("query success, sql: " . $sql . ", modified user: " . $username . ", device token: " . $deviceToken);
        }
        
        return $resultArray;
    }
    
    function doQueryTaskCredentials($con,$sql,$user)
    {
        $res = mysqli_query($con,$sql);
        if(!$res)
        {
            $error = "query fail, sql: " . $sql . ", modified user: " . $user . " error: " . mysqli_error($con);
            writeToLogCredentials($error);
            
            
            // Rollback transaction
            mysqli_rollback($con);
            mysqli_close($con);
            $response = array('status' => $error);
            return $response;
        }
        else
        {
            writeToLogCredentials("query success, sql: " . $sql . ", modified user: " . $user);
        }
        return "";
    }
    
    
    
    

    if (isset ($_POST["username"]) && isset ($_POST["password"]))
    {
        $username = $_POST["username"];
        $password = $_POST["password"];
        $deviceToken = $_POST["modifiedDeviceToken"];
    }
    else
    {
        $username = "MINIMALIST_TEST";
        $password = "1234567890";
        $deviceToken = "test";
    }
    writeToLogCredentials("device token: " . $deviceToken);
    
    
    
    {
        // Check connection
        if (mysqli_connect_errno())
        {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }
        
        
        // Set autocommit to off
        mysqli_autocommit($con,FALSE);
        writeToLogCredentials("set auto commit to off");
        
        
        
        //เช็คว่ามี username นี้มั๊ย -> ไม่มี alert -> username is invalid
        //เช็คว่ามี password ถูกต้องมั๊ย -> ไม่มี alert -> password is invalid
        //เช็คว่า expire หรือยัง
        //ยังไม่ expire -> insert device with this credentials
        //ถ้า else ให้ status => 2 alert -> application is expire, please contact administrator
        //2 tables นี้ ไม่ได้ใช้ในฝั่ง app เลย ใช้เพียงเช็คตอน first setup เท่านั้น
        
        $sql = "select * from Credentials where `Username` = '$username'";
        $selectedRow = getSelectedRowCredentials($sql);
        if(sizeof($selectedRow) == 0)
        {
            writeToLogCredentials("query commit");
            mysqli_commit($con);
            mysqli_close($con);
            $response = array('status' => '2', 'sql' => $sql, 'alert' => 'Username is invalid');
            
            
            echo json_encode($response);
            exit();
        }
        $noOfDeviceAllowed = $selectedRow[0]["NoOfDeviceAllowed"];
        $credentialsID = $selectedRow[0]["CredentialsID"];
        $strExpiredDate = $selectedRow[0]["ExpiredDate"];
        $credentialPassword = $selectedRow[0]["Password"];
        //----
        
        
        if($password != $credentialPassword)
        {
            writeToLogCredentials("query commit");
            mysqli_commit($con);
            mysqli_close($con);
            $response = array('status' => '2', 'sql' => $sql, 'alert' => 'Password is invalid');
            
            
            echo json_encode($response);
            exit();
        }
        
        
        writeToLogCredentials("str expired date: " . $strExpiredDate);
        $expiredDate = date('Y-m-d H:i:s',strtotime($strExpiredDate));
        $currentDate = date('Y-m-d H:i:s');
        writeToLogCredentials("expired date: " . $expiredDate);
        writeToLogCredentials("current date: " . $currentDate);
        if($currentDate >= $expiredDate)
        {
            writeToLogCredentials("query commit");
            mysqli_commit($con);
            mysqli_close($con);
            $response = array('status' => '2', 'sql' => $sql, 'alert' => 'Application is expired, please contact administrator');
            
            
            echo json_encode($response);
            exit();
        }
        //------
        
        
        
        $sql = "insert into `CredentialsDevice` (`CredentialsID`,`DeviceToken`,`CountSetup`) values ($credentialsID, '$deviceToken',1)";
        $ret = doQueryTaskCredentials($con,$sql,$username);
        if($ret != "")
        {
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
//        $credentialsDeviceID = mysqli_insert_id($con);
        
    }
    
    
    
    
    //do script successful    
    writeToLogCredentials("query commit, file: " . basename(__FILE__));
    mysqli_commit($con);
    mysqli_close($con);
    $response = array('status' => '1', 'sql' => $sql);
    

    echo json_encode($response);
    exit();
?>
