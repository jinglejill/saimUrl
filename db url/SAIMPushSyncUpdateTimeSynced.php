<?php
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    
    
    if (isset ($_POST["countPushSync"]))
    {
        $countPushSync = $_POST["countPushSync"];
        for($i=0; $i<$countPushSync; $i++)
        {
            $pushSyncID[$i] = $_POST["pushSyncID".sprintf("%02d", $i)];
            $pushSyncID[$i] = mysqli_real_escape_string($con, $pushSyncID[$i]);
        }
    }
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    //ไม่ต้อง push notification เพราะใน app ไม่มีเรียกใช้ column ที่ update นี้
    $sql = "UPDATE `PushSync` SET `TimeSynced` = now() WHERE pushsyncid in ($pushSyncID[0]";
    for($i=1; $i<$countPushSync; $i++)
    {
        $sql .= ",$pushSyncID[$i]";
    }
    $sql .= ")";
    $res = mysqli_query($con,$sql);
    if($res)
    {
        $response = array('status' => '1', 'sql' => $sql);
        writeToLog("query success, sql: " . $sql . ", modified device: " . $_POST["modifiedDeviceToken"]);
    }
    else
    {
        $error = "query fail, sql: " . $sql . ", modified device: " . $_POST["modifiedDeviceToken"] . " error: " . mysqli_error($con);
        writeToLog($error);
    }
    
    
    if($error != "")
    {
        putAlertToDevice($_POST["modifiedUser"],$_POST["modifiedDeviceToken"]);
    }
    mysqli_close($con);
    echo json_encode($response);
    writeToLog("end of file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    exit();
?>
