<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    
    if (isset ($_POST["countReceiptProductItem"])
        )
    {
        $countReceiptProductItem = $_POST["countReceiptProductItem"];
        $customMadeIn = $_POST["customMadeIn"];
        for($i=0; $i<$countReceiptProductItem; $i++)
        {
            $receiptProductItemID[$i] = $_POST["receiptProductItemID".sprintf("%02d", $i)];
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
        $sql = "UPDATE `receiptProductItem` SET CustomMadeIn='$customMadeIn' WHERE ReceiptProductItemID in ($receiptProductItemID[0]";
        for($i=1; $i<$countReceiptProductItem; $i++)
        {
            $sql .= ", '$receiptProductItemID[$i]'";
        }
        $sql .= ")";
        $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
        if($ret != "")
        {
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
        $wherePart = substr($sql,strpos($sql,'WHERE'),strlen($sql)-strpos($sql,'WHERE')+1);
        
        
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select * from receiptProductItem " . $wherePart;
        $selectedRow = getSelectedRow($sql);
        
        
        //broadcast ไป device token อื่น
        $type = 'tReceiptProductItem';
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
