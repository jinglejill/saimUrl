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
            $modifiedUser[$i] = $_POST["modifiedUser".sprintf("%02d", $i)];
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
    
    
    
    

    //-----
    if($countProduct > 0)
    {
        for($k=0; $k<$countProduct; $k++)
        {
            //productrunningid
            $sql = "INSERT INTO `productrunning`(`ModifiedDate`) VALUES (now())";
            $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
            $newID = mysqli_insert_id($con);
            $newID = sprintf("%06d", $newID);
            
            
            //query statement
            $sql = "INSERT INTO Product(ProductID, ProductCode, ProductCategory2, ProductCategory1, ProductName, Color, Size, ManufacturingDate, Status, Remark, EventID) VALUES ('$newID','$productCode[$k]', '$productCategory2[$k]', '$productCategory1[$k]', '$productName[$k]', '$color[$k]', '$size[$k]', '$manufacturingDate[$k]', '$status[$k]', '$remark[$k]', '$eventID[$k]')";
            $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
            if($ret != "")
            {
                mysqli_rollback($con);
                putAlertToDevice($_POST["modifiedUser"]);
                echo json_encode($ret);
                exit();
            }
            
            
            
//            //insert ผ่าน
//            $newID = mysqli_insert_id($con);
            
            
            
            //**********sync device token ตัวเอง delete old id and insert newID
            //select row ที่แก้ไข ขึ้นมาเก็บไว้
            $sql = "select '$productID[$k]' as ProductID, 1 as ReplaceSelf, '$modifiedUser' as ModifiedUser";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'tProduct';
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
            $productID[$k] = $newID;
            $sql = "select *, 1 IdInserted from Product where ProductID = '$productID[$k]'";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'tProduct';
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
        $sql = "select *, 1 IdInserted from Product where ProductID in ('$productID[0]'";
        for($i=1; $i<$countProduct; $i++)
        {
            $sql .= ",'$productID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token อื่น
        $type = 'tProduct';
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
    //-----
    
    
    
    
    
    //do script successful
    mysqli_commit($con);
    sendPushNotificationToAllDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    writeToLog("query commit, file: " . basename(__FILE__));
    $response = array('status' => '1', 'sql' => $sql);
    
    
    echo json_encode($response);
    exit();
?>
