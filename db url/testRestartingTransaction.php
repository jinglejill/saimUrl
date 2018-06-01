<?php
    include_once('dbConnect.php');
//    setConnectionValue($_POST['dbName']);
    setConnectionValue("MINIMALIST_TEST");
    writeToLog("file: " . basename(__FILE__));
    $queryTime = date('Y-m-d H:i:s');
    
    
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
    $countProduct = 2;
    $eventID[0] = 104;
    $remark[0] = "test try restarting transaction1";
    $productID[0] = "010077";
    $eventID[1] = 104;
    $remark[1] = "test try restarting transaction2";
    $productID[1] = "010078";
    
    
    for($x=1; $x<=3; $x++)
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
            if($ret != "" && $x == 3)
            {
                echo json_encode($ret);
                exit();
            }
            else if($ret != "" && $x != 3)
            {
                continue;
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
        doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action,$queryTime);
        //-----
        
        
        
        $ret = updatePushSyncStatus($queryTime);
        if($ret != "" && $x == 3)
        {
            echo json_encode($ret);
            exit();
        }
        else if($ret != "" && $x != 3)
        {
            continue;
        }
        sendPushNotificationToOtherDevices($_POST["modifiedDeviceToken"]);
        break;
        //-----
    }
 
    
    
    
    //do script successful
    writeToLog("query commit");
    mysqli_commit($con);
    mysqli_close($con);
    $response = array('status' => '1', 'sql' => $sql);
    
    
    echo json_encode($response);
    exit();
?>