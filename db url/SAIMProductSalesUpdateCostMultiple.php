<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    
    if(isset($_POST["countProductSalesID"])
       && isset($_POST["cost"])
       )
    {
        $countProductSalesID = intval($_POST["countProductSalesID"]);
        $cost = $_POST["cost"];
        
        
        for($i=0; $i<$countProductSalesID; $i++)
        {
            $productSalesID[$i] = $_POST["productSalesID".sprintf("%03d", $i)];
        }
    }
    else
    {
        $countProductSalesID = 0;
        $cost = -1;
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
        $sql = "UPDATE `productsales` SET cost=$cost WHERE ProductSalesID in ($productSalesID[0]";
        for($i=1; $i<$countProductSalesID; $i++)
        {
            $sql .= ",$productSalesID[$i]";
        }
        $sql .= ")";
        $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
        if($ret != "")
        {
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
        
        
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = str_replace("UPDATE `productsales` SET cost=$cost","select * from productsales",$sql);
        $selectedRow = getSelectedRow($sql);
        
        
        //broadcast ไป device token อื่น
        $type = 'tProductSales';
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
