<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    printAllPost();
    
    
    if(isset($_POST["countEvent"]) && isset($_POST["userAccountID"]))
    {
        $countEvent = $_POST["countEvent"];
        $userAccountID = $_POST["userAccountID"];
        for($i=0; $i<intval($_POST["countEvent"]); $i++)
        {
            $userAccountEventID[$i] = $_POST["userAccountEventID".sprintf("%02d", $i)];
            $eventID[$i] = $_POST["eventID".sprintf("%02d", $i)];
        }
    }
    $modifiedUser = $_POST["modifiedUser"];


    
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    // Set autocommit to off
    mysqli_autocommit($con,FALSE);
    writeToLog("set auto commit to off");
    
    
    //select row ที่ delete ขึ้นมาเก็บไว้ก่อน
    $sql = "select * from UserAccountEvent WHERE UserAccountID = $userAccountID";
    $selectedRow = getSelectedRow($sql);
    
    
    //query statement
    $sql = "delete from `UserAccountEvent` where userAccountEventID in (select userAccountEventID from (select userAccountEventID from UserAccountEvent where `UserAccountID`= $userAccountID order by userAccountEventID) tempTable)";
    $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    
    
    //broadcast ไป device token อื่น
    $type = 'tUserAccountEvent';
    $action = 'd';
    $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    
    
    
    if($countEvent > 0)
    {
        for($k=0; $k<$countEvent; $k++)
        {
            
            
            //query statement
            $sql = "INSERT INTO UserAccountEvent(UserAccountID, EventID) VALUES ('$userAccountID', '$eventID[$k]')";
            $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
            if($ret != "")
            {
                mysqli_rollback($con);
                putAlertToDevice($_POST["modifiedUser"]);
                echo json_encode($ret);
                exit();
            }
            
            
            
            //insert ผ่าน
            $newID = mysqli_insert_id($con);
            writeToLog("newid: ",$newID);
            
            
            //**********sync device token ตัวเอง delete old id and insert newID
            //select row ที่แก้ไข ขึ้นมาเก็บไว้
            $sql = "select $userAccountEventID[$k] as UserAccountEventID, 1 as ReplaceSelf, '$modifiedUser' as ModifiedUser";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'tUserAccountEvent';
            $action = 'd';
            $ret = doPushNotificationTaskToDevice($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
            if($ret != "")
            {
                mysqli_rollback($con);
                putAlertToDevice($_POST["modifiedUser"]);
                echo json_encode($ret);
                exit();
            }
            
            
            
            //select row ที่แก้ไข ขึ้นมาเก็บไว้
            $userAccountEventID[$k] = $newID;
            $sql = "select *, 1 IdInserted from UserAccountEvent where UserAccountEventID = '$userAccountEventID[$k]'";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'tUserAccountEvent';
            $action = 'i';
            $ret = doPushNotificationTaskToDevice($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
            if($ret != "")
            {
                mysqli_rollback($con);
                putAlertToDevice($_POST["modifiedUser"]);
                echo json_encode($ret);
                exit();
            }
        }
        
        
        
        //**********sync device token อื่น
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select *, 1 IdInserted from UserAccountEvent where UserAccountEventID in ('$userAccountEventID[0]'";
        for($i=1; $i<$countEvent; $i++)
        {
            $sql .= ",'$userAccountEventID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        
        
        //broadcast ไป device token อื่น
        $type = 'tUserAccountEvent';
        $action = 'i';
        $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
    }
    
    
    
    
    //do script successful
    mysqli_commit($con);
    sendPushNotificationToAllDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    writeToLog("query commit, file: " . basename(__FILE__));
    $response = array('status' => '1', 'sql' => $sql);
    
    
    echo json_encode($response);
    exit();
?>
