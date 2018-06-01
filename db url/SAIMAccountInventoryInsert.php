<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));    
    
    
    if (isset ($_POST["countAccountInventory"]))
    {
        
        $countAccountInventory = $_POST["countAccountInventory"];
        writeToLog('post countAccountInventory count: ' . $countAccountInventory);
        for($i=0; $i<$countAccountInventory; $i++)
        {
            $accountInventoryID[$i] = $_POST["accountInventoryID".sprintf("%02d", $i)];
            $productNameID[$i] = $_POST["productNameID".sprintf("%02d", $i)];
            $quantity[$i] = $_POST["quantity".sprintf("%02d", $i)];
            $status[$i] = $_POST["status".sprintf("%02d", $i)];
            $inOutDate[$i] = $_POST["inOutDate".sprintf("%02d", $i)];
            writeToLog('enter loop countAccountInventory i: ' . $i . ', quantity: ' . $quantity[$i]);
        }
    }
    else
    {
        writeToLog('not post countAccountInventory');
    }
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    // Set autocommit to off
    mysqli_autocommit($con,FALSE);
    writeToLog("set auto commit to off");
    
    
    $sql = "select ifnull(max(AccountInventoryID),0) MaxAccountInventoryID from `AccountInventory`";
    $selectedRow = getSelectedRow($sql);
    $maxAccountInventoryID = $selectedRow[0]["MaxAccountInventoryID"];
    $startAccountInventoryID = $maxAccountInventoryID+1;
    
    
    $sql = "INSERT INTO `accountinventory`(`AccountInventoryID`, `ProductNameID`, `Quantity`, `Status`, `InOutDate`) values($startAccountInventoryID,$productNameID[0],$quantity[0],$status[0],'$inOutDate[0]')";
    for($i=1; $i<$countAccountInventory; $i++)
    {
        //query statement
        $sql .= ",($startAccountInventoryID+$i,$productNameID[$i],$quantity[$i],$status[$i],'$inOutDate[$i]')";
    }
    
    $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    
    
    //do script successful
    writeToLog("query commit, file: " . basename(__FILE__));
    mysqli_commit($con);
    mysqli_close($con);
    $response = array('status' => '1', 'sql' => $sql);


    echo json_encode($response);
    exit();
?>
