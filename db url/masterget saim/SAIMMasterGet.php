<?php
    include_once('./dbConnect.php');
//    setConnectionValue($_POST['dbName']);
    setConnectionValue('MINIMALIST_TEST');
    ini_set("memory_limit","80M");
    writeToLog("file: " . basename(__FILE__));
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    //check expired date
    $sql = "SELECT * FROM Setting where enumKey = 'expiredDate'";
    $selectedRow = getSelectedRow($sql);
    $expiredDate = date('Y-m-d H:i:s',strtotime($selectedRow[0]["Value"]));
    $currentDate = date('Y-m-d H:i:s');
    
    if($currentDate >= $expiredDate)
    {
        $arrOfTableArray = array();
        echo json_encode($arrOfTableArray);
        mysqli_close($con);
        exit();
    }
    
    

    $sql = "SELECT * FROM UserAccount order by Username;";
//    $sql .= "SELECT * FROM ProductName;";
//    $sql .= "SELECT * FROM Color;";
    $sql .= "SELECT * FROM `product` WHERE Status in ('I','P') or (Status='S' AND productid in (SELECT productid FROM ReceiptProductItemlimit WHERE ProductType in ('I','A','P','D','S','F','R'))) OR (productID IN (SELECT productid FROM `maxproductid` WHERE 1));";
    $sql .= "SELECT * from Event;";
    $sql .= "SELECT * FROM UserAccountEvent;";
    $sql .= "SELECT * FROM ProductCategory2;";
    $sql .= "SELECT * FROM ProductCategory1;";
    $sql .= "select * FROM ProductSales;";
    $sql .= "SELECT * FROM `CashAllocation`;";
    $sql .= "SELECT * FROM `CustomMade`;";
    $sql .= "SELECT * FROM `Receipt` WHERE (receiptID in (SELECT receiptid FROM ReceiptProductItemlimit)) OR (receiptID IN (SELECT receiptID FROM `maxreceiptid` WHERE 1));";
    $sql .= "SELECT * FROM ReceiptProductItem where receiptproductitemid in (select receiptproductitemid from receiptproductitemlimit) or (receiptProductItemID IN (SELECT receiptProductItemID FROM `maxreceiptProductItemid` WHERE 1));";
    $sql .= "SELECT * FROM `compareinventoryhistory` where compareinventoryhistoryID in (select compareinventoryhistoryid from compareinventoryhistorylimit) OR (compareInventoryHistoryID IN (SELECT compareInventoryHistoryID FROM `maxCompareInventoryHistoryid` WHERE 1));";
    $sql .= "SELECT * FROM `compareinventory` WHERE `RunningSetNo` in (select compareinventoryhistoryid from compareinventoryhistorylimit) OR (compareInventoryid IN (SELECT compareInventoryid FROM `maxcompareInventoryid` WHERE 1));";
    $sql .= "SELECT * FROM `ProductSalesSet` WHERE 1;";
    $sql .= "SELECT * FROM `CustomerReceipt` WHERE 1;";
    $sql .= "SELECT * FROM `PostCustomer` WHERE 1;";
    $sql .= "SELECT * FROM `ProductCost` WHERE 1;";
    $sql .= "SELECT * FROM `EventCost` WHERE 1;";
    $sql .= "SELECT * FROM `CostLabel` WHERE 1;";
    $sql .= "SELECT * FROM `ProductSize` WHERE 1;";
    $sql .= "SELECT case when max(RunningID) is null then 0 else max(RunningID) end as RunningID FROM `ImageRunningID`;";
    $sql .= "SELECT * FROM `ProductDelete` order by productdeleteid desc limit 100;";
    $sql .= "SELECT * FROM `Setting` WHERE 1;";
//    $sql .= "SELECT PushSyncID FROM `PushSync` WHERE DeviceToken = '$deviceToken' and Status = 1 ";
    $sql .= "SELECT * FROM `Postcode` WHERE zone in (1,2);";
    $sql .= "SELECT * FROM RewardPoint;";
    $sql .= "SELECT * FROM `rewardprogram` WHERE `DateStart` <= curdate() and DateEnd >= curdate() UNION select * from rewardprogram WHERE RewardProgramID in (select max(RewardProgramID) FROM rewardprogram);";
    
    
    
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
