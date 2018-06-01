<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    
    if (
        isset ($_POST["countProduct"])
        )
    {
        $countProduct = $_POST["countProduct"];
        for($i=0; $i<$countProduct; $i++)
        {
            $productID[$i] = $_POST["productID".sprintf("%02d", $i)];
            $productCode[$i] = $_POST["productCode".sprintf("%02d", $i)];
            $productCategory2[$i] = $_POST["productCategory2".sprintf("%02d", $i)];
            $productCategory1[$i] = $_POST["productCategory1".sprintf("%02d", $i)];
            $productName[$i] = $_POST["productName".sprintf("%02d", $i)];
            $color[$i] = $_POST["color".sprintf("%02d", $i)];
            $size[$i] = $_POST["size".sprintf("%02d", $i)];
            $manufacturingDate[$i] = $_POST["manufacturingDate".sprintf("%02d", $i)];
            $status[$i] = $_POST["status".sprintf("%02d", $i)];
            $remark[$i] = $_POST["remark".sprintf("%02d", $i)];
            $eventID[$i] = $_POST["eventID".sprintf("%02d", $i)];
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
        
        
        for($i=0; $i<$countProduct; $i++)
        {
            //query statement
            $sql = "update product set eventID = $eventID[$i], remark = '$remark[$i]' where productID = '$productID[$i]'";
            $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
            if($ret != "")
            {
                putAlertToDevice($_POST["modifiedUser"]);
                echo json_encode($ret);
                exit();
            }
            
        }
        
        
        
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select * from product where `ProductID` in ('$productID[0]'";
        for($i=1; $i<$countProduct; $i++)
        {
            $sql .= ",'$productID[$i]'";
        }
        $sql .= ")";
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
