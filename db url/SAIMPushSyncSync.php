<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    

    if (isset($_POST["deviceToken"]))
    {
        $deviceToken = $_POST["deviceToken"];
    }
    else
    {
        $deviceToken = "117235362ed8da69f127c5c80073aeb2bb397de5165bed59bb62bafac5bda28b";//iphone
//        $deviceToken = "dd28fe4682fedff4b7047efc161d6ad3ff807abd03a6a3516e9a21d3d06062bc";//ipad
    }
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    $sql = "select * from PushSync where DeviceToken = '$deviceToken' and TimeSynced = '0000-00-00 00:00:00'";
    $pushSyncList = getSelectedRow($sql);
    
    

    writeToLog('push sync list: ' . json_encode($pushSyncList));
    $arrParamBody = array();
    foreach($pushSyncList as $row)
    {
        if(strcmp($row["TableName"],"sProductSales") == 0)
        {
            //select row ที่แก้ไข ขึ้นมาเก็บไว้
            $selectedRow = getSelectedRow($row["Data"]);
            $row["TableName"] = "tProductSales";
            writeToLog("productsales insert: ".json_encode($selectedRow));
        }
        else if(strcmp($row["TableName"],"sCompareInventory") == 0)
        {
            //select row ที่แก้ไข ขึ้นมาเก็บไว้
            $selectedRow = getSelectedRow($row["Data"]);
            $row["TableName"] = "tCompareInventory";
            writeToLog("CompareInventory insert: ".json_encode($selectedRow));
        }
        else
        {
            $selectedRow = json_decode($row["Data"], true);
        }
        
        
        $paramBody = array(
                           'type' => $row["TableName"],
                           'data' => $selectedRow,
                           'action' => $row["Action"],
                           'pushSyncID' => $row["PushSyncID"]
                           );
        array_push($arrParamBody, $paramBody);
    }
    

    if(sizeof($pushSyncList) == 0)
    {
        $response = array('status' => '0');
    }
    else
    {
        $response = array('status' => '1', 'payload' => $arrParamBody);
    }
    
    
    mysqli_close($con);
    echo json_encode($response);
    exit();
?>
