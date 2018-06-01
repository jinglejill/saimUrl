<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    
    if (isset ($_POST["eventID"])
        )
    {
        $eventID = intval($_POST["eventID"]);
    }
    else
    {
        $eventID = -1;
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
        
        
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select * from product where Status = 'I' and `EventID` = $eventID";
        $selectedRow = getSelectedRow($sql);
        
        
        //query statement
        //
        $sql = "UPDATE `product` SET `EventID` = 0 WHERE productID in (select productID from (select productID from product where Status = 'I' and `EventID` = $eventID) tempTable)";
        $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
        if($ret != "")
        {
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
        
        
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $countSelectedRow = sizeof($selectedRow);
        if($countSelectedRow > 0)
        {
            $sql = "select * from product where `ProductID` in (";
            for($i=0; $i<$countSelectedRow; $i++)
            {
                $productIDEdit = $selectedRow[$i]["ProductID"];
                $sql .= "'$productIDEdit',";
            }
            $sql = substr($sql,0,strlen($sql)-1);
            $sql = $sql . ")";
        }
        $selectedRow = getSelectedRow($sql);
        
        
        //broadcast ไป device token อื่น
        $type = 'tProduct';
        $action = 'u';
        $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
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
