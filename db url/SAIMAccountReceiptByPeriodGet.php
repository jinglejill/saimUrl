<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    ini_set("memory_limit","50M");
    writeToLog("file: " . basename(__FILE__));
    
    
    if(isset($_POST["dateFrom"]) && isset($_POST["dateTo"])
       )
    {
        $dateFrom = $_POST["dateFrom"];
        $dateTo = $_POST["dateTo"];
    }
    else
    {
        $dateFrom = '2017-02-06';
        $dateTo = '2017-02-10';
    }
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    

    $sql = "select * FROM `accountReceipt` where date_format(accountreceipt.ReceiptDate,'%Y-%m-%d') BETWEEN '$dateFrom' AND '$dateTo';";
    $selectedRow = getSelectedRow($sql);
    $countSelectedRow = sizeof($selectedRow);

    if($countSelectedRow > 0)
    {
        $accountReceiptID = $selectedRow[0]["AccountReceiptID"];
        $sql .= "select * FROM `accountReceiptProductItem` where accountReceiptID in ($accountReceiptID";
        for($i=1; $i<$countSelectedRow; $i++)
        {
            $accountReceiptID = $selectedRow[$i]["AccountReceiptID"];
            $sql .= ",$accountReceiptID";
        }
        $sql .= ")";
    }
    
//    $sql .= "select * FROM `accountReceipt` where runningAccountReceiptHistory = $runningAccountReceiptHistory;";
    
    
    writeToLog("accountReceiptByPeriodGet sql: " . $sql);
    
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
