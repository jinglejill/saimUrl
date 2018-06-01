<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);    
    ini_set("memory_limit","50M");
    writeToLog("file: " . basename(__FILE__));

        
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    $sql = "select @i:=@i+1 AS Row,tblTen.* from (SELECT transferHistory.*, Event.location EventName, EventDestination.location EventNameDestination from transferHistory left join Event on transferHistory.eventID = Event.eventID left join Event as EventDestination on transferHistory.eventIDDestination = EventDestination.eventID order by transferHistory.transferDate desc limit 10) tblTen, (SELECT @i:=0) AS tblIterator";
    writeToLog("transferHistoryGet sql: " . $sql);
    
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
