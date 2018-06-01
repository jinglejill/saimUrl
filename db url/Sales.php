<?php
    include_once('./dbConnect.php');
    setConnectionValue('DOROTA');
    ini_set("memory_limit","-1");
    writeToLog("file: " . basename(__FILE__));
    
    
    if(isset($_GET["startDate"]) && isset($_GET["endDate"]))
    {
        $startDate = $_GET['startDate'];
        $endDate = $_GET['endDate'];
    }
    else
    {
        $startDate = date("Y-m-d");
        $endDate = date("Y-m-d");
    }
    
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
    writeToLog("not expire");
    

    $sql = "SELECT SalesUser Name,ifnull(sum(payprice),0) Sales FROM `receipt` WHERE date_format(ReceiptDate,'%Y-%m-%d') BETWEEN '$startDate' and '$endDate' GROUP by SalesUser;";
    
    writeToLog("sql = " . $sql);
    
    
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
        

        echo "<table border='1' style='border-collapse: collapse;'>";
        for($i=0; $i<sizeof($arrOfTableArray[0]); $i++)
        {
            $row = $arrOfTableArray[0][$i];
            echo "<tr><td>" . $row->Name . "</td><td>" . number_format($row->Sales) . "</td></tr>";
        }
        echo "</table>";


    }

    
    // Close connections
    mysqli_close($con);
    
    
    
    ?>
