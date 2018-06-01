<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    
    if(isset($_POST["countEventCost"]) && isset($_POST["eventID"]))
    {
        $eventID = $_POST["eventID"];
        $countEventCost = $_POST["countEventCost"];
        
        
        for($i=0; $i<intval($countEventCost); $i++)
        {
            $eventCostID[$i] = $_POST["eventCostID".sprintf("%02d", $i)];
            $costLabelID[$i] = $_POST["costLabelID".sprintf("%02d", $i)];
            $costLabel[$i] = $_POST["costLabel".sprintf("%02d", $i)];
            $cost[$i] = $_POST["cost".sprintf("%02d", $i)];
        }
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
        
        
        //select row ที่ delete ขึ้นมาเก็บไว้ก่อน
        $sql = "select * from eventcost where `EventID` = $eventID";
        $selectedRow = getSelectedRow($sql);
        
        
        //query statement
        $sql = "DELETE FROM `eventcost` WHERE eventcostID in (select eventcostID from (select eventcostID from eventcost where eventid = $eventID order by eventcostid) tempTable)";
        $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
        if($ret != "")
        {
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
        
        
        //broadcast ไป device token อื่น
        $type = 'tEventCost';
        $action = 'd';
        $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
        //-----
        
        
        
        if(intval($countEventCost)>0)
        {
            //query statement
            $sql = "INSERT INTO `eventcost` (`EventCostID`, `EventID`, `CostLabelID`, `CostLabel`, `Cost`) VALUES ";
            for($i=0; $i<intval($_POST["countEventCost"]); $i++)
            {
                $sql .= "($eventCostID[$i],$eventID,'$costLabelID[$i]','$costLabel[$i]',$cost[$i]),";
            }
            $sql = substr($sql,0,strlen($sql)-1);
            $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
            if($ret != "")
            {
                putAlertToDevice($_POST["modifiedUser"]);
                echo json_encode($ret);
                exit();
            }
            
            
            
            //select row ที่แก้ไข ขึ้นมาเก็บไว้
            $sql = "select * from eventcost where `EventID` = $eventID";
            $selectedRow = getSelectedRow($sql);
            
            
            //broadcast ไป device token อื่น
            $type = 'tEventCost';
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
