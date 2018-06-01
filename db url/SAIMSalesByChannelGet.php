<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    ini_set("memory_limit","50M");
    writeToLog("file: " . basename(__FILE__));
    
    
    if(isset($_POST["periodFrom"]) &&
       isset($_POST["periodTo"])
       )
    {
        $periodFrom = $_POST["periodFrom"];
        $periodTo = $_POST["periodTo"];
    }
    else
    {
        $periodFrom = "2016-07-08";
        $periodTo = "2016-07-09";
    }
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    $sql = "SELECT Channel, sum(PayPrice) Sales FROM `receipt` WHERE ReceiptDate BETWEEN '$periodFrom' and '$periodTo' GROUP BY Channel;";
    writeToLog("salesByChannel sql: " . $sql);
    
    
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
