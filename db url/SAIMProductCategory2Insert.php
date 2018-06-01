<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    
    if(isset($_POST["count"])
       )
    {
        $count = intval($_POST["count"]);
        
        
        for($i=0; $i<$count; $i++)
        {
            $code[$i] = $_POST["code".sprintf("%02d", $i)];
            $name[$i] = $_POST["name".sprintf("%02d", $i)];
        }
    }
    else
    {
        $count = 0;
    }
    
    
    
    {
        // Check connection
        if (mysqli_connect_errno())
        {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }
        // Set autocommit to off
        mysqli_autocommit($con,FALSE);
        writeToLog("set auto commit to off");
        
        
        for($i=0; $i<$count; $i++)
        {
            //query statement
            $sql = "insert into `productcategory2` (`name`,`code`) values('$name[$i]', '$code[$i]')";
            $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
            if($ret != "")
            {
                putAlertToDevice($_POST["modifiedUser"]);
                echo json_encode($ret);
                exit();
            }
            
            
            
            //select row ที่แก้ไข ขึ้นมาเก็บไว้
            $sql = "select * from productcategory2 where `Code` = $code[$i]";
            $selectedRow = getSelectedRow($sql);
            
            
            //broadcast ไป device token อื่น
            $type = 'tProductCategory2';
            $action = 'i';
            $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
            if($ret != "")
            {
                putAlertToDevice($_POST["modifiedUser"]);
                echo json_encode($ret);
                exit();
            }
            
            //-----
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
