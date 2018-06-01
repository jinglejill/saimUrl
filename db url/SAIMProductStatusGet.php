<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);    
    ini_set("memory_limit","50M");
    writeToLog("file: " . basename(__FILE__));
    
    
    if(isset($_POST["productCategory2"]) &&
       isset($_POST["productCategory1"]) &&
       isset($_POST["productName"]) &&
       isset($_POST["color"]) &&
       isset($_POST["size"]) &&
       isset($_POST["status"])
       )
    {
        $productCategory2 = $_POST["productCategory2"];
        $productCategory1 = $_POST["productCategory1"];
        $productName = $_POST["productName"];
        $color = $_POST["color"];
        $size = $_POST["size"];
        $status = $_POST["status"];
        
    }
    else
    {
        $productCategory2 = "01";
        $productCategory1 = "01";
        $productName = "05";
        $color = "01";
        $size = "35";
        $status = "S";
    }
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    //I, S, R
    $sql = "SELECT * FROM `product` WHERE productcategory2 = '$productCategory2' and productcategory1 = '$productCategory1' and productname = '$productName' and color = '$color' and size = '$size' and status = '$status';";
    $sql .= "SELECT Receipt.* FROM product left join ReceiptProductItem on product.productid = receiptproductitem.productid left join receipt on receiptproductitem.receiptID = receipt.receiptID where productcategory2 = '$productCategory2' and productcategory1 = '$productCategory1' and productname = '$productName' and color = '$color' and size = '$size' and status = '$status' and receiptproductitem.productType in ('I','S','R');";
    $sql .= "SELECT ReceiptProductItem.* FROM product left join ReceiptProductItem on product.productid = receiptproductitem.productid where productcategory2 = '$productCategory2' and productcategory1 = '$productCategory1' and productname = '$productName' and color = '$color' and size = '$size' and status = '$status' and receiptproductitem.productType in ('I','S','R');";
    writeToLog("salesdetail sql: " . $sql);
    
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