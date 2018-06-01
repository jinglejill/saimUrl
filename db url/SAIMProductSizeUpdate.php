<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    if(isset($_POST["count"])
       )
    {
        $count = intval($_POST["count"]);
        
        
        for($i=0; $i<$count; $i++)
        {
            $code[$i] = $_POST["code".sprintf("%02d", $i)];
            $sizeLabel[$i] = $_POST["sizeLabel".sprintf("%02d", $i)];
            $sizeOrder[$i] = $_POST["sizeOrder".sprintf("%02d", $i)];
        }
    }
    else
    {
        $count = 0;
    }
    
    
    
    {
        // Set autocommit to off
        mysqli_autocommit($con,FALSE);
        writeToLog("set auto commit to off");
        
        
        for($i=0; $i<$count; $i++)
        {
            //query statement
            $sql = "UPDATE `productsize` SET sizeLabel='$sizeLabel[$i]', sizeOrder=$sizeOrder[$i] WHERE code = '$code[$i]';";
            $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
            if($ret != "")
            {
                putAlertToDevice($_POST["modifiedUser"]);
                echo json_encode($ret);
                exit();
            }
            
            
            
            //select row ที่แก้ไข ขึ้นมาเก็บไว้
            $sql = "select * from productsize where code = '$code[$i]'";
            $selectedRow = getSelectedRow($sql);
            
            
            //broadcast ไป device token อื่น
            $type = 'tProductSize';
            $action = 'u';
            $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
            if($ret != "")
            {
                putAlertToDevice($_POST["modifiedUser"]);
                echo json_encode($ret);
                exit();
            }
            
        }
        //-----
        
    }
    
    
    
    //do script successful
    mysqli_commit($con);
    sendPushNotificationToOtherDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    writeToLog("query commit, file: " . basename(__FILE__));
    $response = array('status' => '1', 'sql' => $sql);
    
    
    echo json_encode($response);
    exit();
?>
