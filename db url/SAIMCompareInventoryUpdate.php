<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    
    if (isset ($_POST["countCompareInventory"])
        )
    {
        $countCompareInventory = $_POST["countCompareInventory"];
        $runningSetNo = $_POST["runningSetNo"];
//        $compareStatus = $_POST["compareStatus"];
        for($i=0; $i<$countCompareInventory; $i++)
        {
            $compareStatus[$i] = $_POST["compareStatus".sprintf("%02d", $i)];
            $compareStatusRemark[$i] = $_POST["compareStatusRemark".sprintf("%02d", $i)];
            $productID[$i] = $_POST["productID".sprintf("%02d", $i)];
        }
    }
    //test
    if($compareStatus[0] == "")
    {
        for($i=0; $i<$countCompareInventory; $i++)
        {
            $compareStatus[$i] = $_POST["compareStatus"];
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
        
        
        
        //query statement
//        $sql = "UPDATE `compareinventory` SET CompareStatus='$compareStatus', CompareStatusRemark='$compareStatusRemark' WHERE RunningSetNo = $runningSetNo and ProductID in ('$productID[0]'";
//        for($i=1; $i<$countCompareInventory; $i++)
//        {
//            $sql .= ", '$productID[$i]'";
//        }
//        $sql .= ")";
        
        
        for($i=0; $i<$countCompareInventory; $i++)
        {
            $sql = "UPDATE `compareinventory` SET CompareStatus='$compareStatus[$i]', CompareStatusRemark='$compareStatusRemark[$i]' WHERE RunningSetNo = $runningSetNo and ProductID = '$productID[$i]'";
            $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
            if($ret != "")
            {
                putAlertToDevice($_POST["modifiedUser"]);
                echo json_encode($ret);
                exit();
            }
            
        }
        
        
        $sql = "select * from compareinventory  WHERE RunningSetNo = $runningSetNo and ProductID in ('$productID[0]'";
        for($i=1; $i<$countCompareInventory; $i++)
        {
            $sql .= ", '$productID[$i]'";
        }
        $sql .= ")";

        
//        $wherePart = substr($sql,strpos($sql,'WHERE'),strlen($sql)-strpos($sql,'WHERE')+1);
        
        
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
//        $sql = "select * from compareinventory " . $wherePart; // where RunningSetNo = $runningSetNo and ProductID = '$productID'";
        $selectedRow = getSelectedRow($sql);
        
        
        //broadcast ไป device token อื่น
        $type = 'tCompareInventory';
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
