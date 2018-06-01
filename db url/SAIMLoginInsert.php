<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    if (isset ($_POST["username"]) &&
        isset ($_POST["status"]) &&
        isset ($_POST["deviceToken"])
        )
    {
        $username = $_POST["username"];
        $status = $_POST["status"];
        $deviceToken = $_POST["deviceToken"];
    }
    else
    {
        $username = "-";
        $status = -1;
        $deviceToken = "-";
    }
    
    
    
    {
        // Check connection
        if (mysqli_connect_errno())
        {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }
        
        
        //query statement
        //ไม่ต้อง push notification เพราะใน app ไม่มีเรียกใช้ column ที่ update นี้
        $sql = "insert into `Login` (`Username`,`Status`,`DeviceToken`) values ('$username',$status,'$deviceToken')";
        $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
        if($ret != "")
        {
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
        
    }
    
    
    
    //do script successful
    $response = array('status' => '1', 'sql' => $sql);
    echo json_encode($response);
    exit();
?>
