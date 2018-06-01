<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    if (isset ($_POST["deviceToken"])
        )
    {
        $deviceToken = $_POST["deviceToken"];
    }
    else
    {
        $deviceToken = "-";
    }
    
    
    
    {
        // Check connection
        if (mysqli_connect_errno())
        {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }
        
        
        //ไม่ต้อง push notification เพราะใน app ไม่มีเรียกใช้ column ที่ update นี้
//        $sql = "UPDATE `PushSync` SET `TimeSynced` = now() WHERE pushsyncid in (select pushsyncid from (select pushsyncid from pushsync where DeviceToken = '$deviceToken' and TimeSynced = '0000-00-00 00:00:00' and Status = 0) tempTable)";
        $sql = "UPDATE `PushSync` SET `TimeSynced` = now() WHERE DeviceToken = '$deviceToken' and TimeSynced = '0000-00-00 00:00:00'";
        $res = mysqli_query($con,$sql);
        if($res)
        {
            $response = array('status' => '1', 'sql' => $sql);
            writeToLog("query success, sql: " . $sql . ", modified user: " . $_POST["modifiedUser"]);            
        }
        else
        {
            $error = "query fail, sql: " . $sql . ", modified user: " . $_POST["modifiedUser"] . " error: " . mysqli_error($con);
            writeToLog($error);
        }        
    }
    if($error != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
    }
    mysqli_close($con);
    echo json_encode($response);
    exit();
?>
