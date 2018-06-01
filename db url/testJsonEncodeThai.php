<?php
    include_once('dbConnect.php');
//    setConnectionValue($_POST['dbName']);
    setConnectionValue('MINIMALISTNEW');
    writeToLog("file: " . basename(__FILE__));
    header("content-type:text/javascript;charset=utf-8");
    
    
    
//    if(isset($_POST["receiptID"]) &&
//       isset($_POST["remark"])
//       )
//    {
//        $receiptID = intval($_POST["receiptID"]);
//        $remark = $_POST["remark"];
//    }
//    else
//    {
        $receiptID = 10557;
//        $remark = "-";
//    }
    
    
    
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
        $sql = "UPDATE `receipt` SET remark='ส่งตาม วันพุธ' WHERE receiptID = $receiptID";
        $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
        if($ret != "")
        {
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
        
        
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select * from receipt where receiptID = $receiptID";
        $selectedRow = getSelectedRow($sql);
        
        
        //broadcast ไป device token อื่น
        $type = 'tReceipt';
        $action = 'u';
        {
            echo json_encode($selectedRow);
//                            $data = iconv("tis-620","utf-8",var_dump($selectedRow));
//            echo "selectedRow[0]['Remark']: " . $selectedRow[0]["Remark"];
//            echo "data: " . $data;
                            $sql = "insert into pushSync (DeviceToken, TableName, Action, Data, TimeSync) values ('$iDeviceToken','$type','$action','" . $data . "',now())";
//            $data = iconv("tis-620","utf-8",$selectedRow);
//            $sql = "insert into pushSync (DeviceToken, TableName, Action, Data, TimeSync) values ('$iDeviceToken','$type','$action','" . json_encode($data, true) . "',now())";

            
            
            //            $sql = "insert into pushSync (DeviceToken, TableName, Action, Data, TimeSync) values ('$iDeviceToken','$type','$action','" . json_encode($selectedRow, true) . "',now())";
        }
        $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
        if($ret != "")
        {
            return $ret;
        }
        
        
        
//        
//        $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
//        if($ret != "")
//        {
//            putAlertToDevice($_POST["modifiedUser"]);
//            echo json_encode($ret);
//            exit();
//        }
        
        //-----
        
    }
    
    
    
    //do script successful
//    sendPushNotificationToOtherDevices($_POST["modifiedDeviceToken"]);
    writeToLog("query commit, file: " . basename(__FILE__));
    mysqli_commit($con);
    mysqli_close($con);
    $response = array('status' => '1', 'sql' => $sql);
    
    
    echo json_encode($response);
    exit();
?>
