<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    ini_set("memory_limit","50M");
    writeToLog("file: " . basename(__FILE__));
    
    
    if(isset($_POST["productCategory2"]) && isset($_POST["transferHistoryID"])
       )
    {
        $productCategory2 = $_POST["productCategory2"];
        $transferHistoryID = $_POST["transferHistoryID"];
    }
    else
    {
        $productCategory2 = '00';
        $transferHistoryID = 1;
    }
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    $sql = "SELECT *, concat(producttransfer.ProductCategory2,producttransfer.ProductCategory1,producttransfer.ProductName,producttransfer.Color,producttransfer.Size) ProductIDGroup FROM `producttransfer` WHERE productCategory2 = '$productCategory2' AND TransferHistoryID = $transferHistoryID ORDER BY concat(producttransfer.ProductCategory2,producttransfer.ProductCategory1,producttransfer.ProductName,producttransfer.Color,producttransfer.Size)";
    
    writeToLog("productTransferGet sql: " . $sql);
    
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
