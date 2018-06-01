<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
//    setConnectionValue('MINIMALIST_TEST');
    ini_set("memory_limit","50M");
    writeToLog("file: " . basename(__FILE__));
    
    
    if (isset($_POST["countReceipt"]))
    {
        $countReceipt = $_POST["countReceipt"];
        for($i=0; $i<$countReceipt; $i++)
        {
            $receiptID[$i] = $_POST["receiptID".sprintf("%03d", $i)];
        }
    }
    $accountYearMonth = $_POST["accountYearMonth"];
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    $sql = "select customerreceipt.ReceiptID, ifnull(postcustomer.TaxCustomerName,'')TaxCustomerName,ifnull(postcustomer.TaxCustomerAddress,'')TaxCustomerAddress,ifnull(postcustomer.TaxNo,'')TaxNo from customerreceipt LEFT JOIN postcustomer ON customerreceipt.PostCustomerID = postcustomer.PostCustomerID where receiptid in ($receiptID[0]";
    for($i=1; $i<$countReceipt; $i++)
    {
        $sql .= ",$receiptID[$i]";
    }
    $sql .= ");";
    $sql .= "select (SELECT ifnull(max(RunningReceiptNo),0) FROM `AccountReceipt` WHERE date_format(receiptdate,'%Y-%m') = '$accountYearMonth') MaxRunningReceiptNo,(select ifnull(max(RunningAccountReceiptHistory),0) from `AccountReceipt`) MaxRunningAccountReceiptHistory, (select ifnull(max(AccountReceiptID),0) from `AccountReceipt`) MaxAccountReceiptID;";
    $sql .= "select ifnull(max(AccountReceiptProductItemID),0) MaxAccountReceiptProductItemID from `AccountReceiptProductItem`;";
    $sql .= "select ifnull(max(AccountMappingID),0) MaxAccountMappingID from `AccountMapping`;";
    $sql .= "select ifnull(max(AccountInventoryID),0) MaxAccountInventoryID from `AccountInventory`;";
    
    
    writeToLog("PostCustomerByReceiptIDGet sql: " . $sql);
    
    /* execute multi query */
    if (mysqli_multi_query($con, $sql)) {
        $arrOfTableArray = array();
        $resultArray = array();
        do {
            /* store first result set */
            if ($result = mysqli_store_result($con)) {
                while ($row = mysqli_fetch_object($result)) {
                    array_push($resultArray, $row);
                }
                array_push($arrOfTableArray,$resultArray);
                $resultArray = [];
                mysqli_free_result($result);
            }
            if(!mysqli_more_results($con))
            {
                break;
            }
        } while (mysqli_next_result($con));
        
        echo json_encode($arrOfTableArray);
    }
    
    
    // Close connections
    mysqli_close($con);
?>
