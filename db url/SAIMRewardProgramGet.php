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
    

    $sql = "SELECT * FROM `rewardprogram` where type = 1 and ((date_format(DateStart,'%Y-%m-%d') <= '$dateFrom' AND date_format(DateEnd,'%Y-%m-%d') >= '$dateTo') or (date_format(DateStart,'%Y-%m-%d') >= '$dateFrom' AND date_format(DateEnd,'%Y-%m-%d') <= '$dateTo') or (date_format(DateStart,'%Y-%m-%d') <= '$dateFrom' AND date_format(DateEnd,'%Y-%m-%d') >= '$dateFrom') or (date_format(DateStart,'%Y-%m-%d') <= '$dateTo' AND date_format(DateEnd,'%Y-%m-%d') >= '$dateTo')) ORDER BY DateStart, DateEnd;";
    $sql .= "SELECT * FROM `rewardprogram` where type = -1 and ((date_format(DateStart,'%Y-%m-%d') <= '$dateFrom' AND date_format(DateEnd,'%Y-%m-%d') >= '$dateTo') or (date_format(DateStart,'%Y-%m-%d') >= '$dateFrom' AND date_format(DateEnd,'%Y-%m-%d') <= '$dateTo') or (date_format(DateStart,'%Y-%m-%d') <= '$dateFrom' AND date_format(DateEnd,'%Y-%m-%d') >= '$dateFrom') or (date_format(DateStart,'%Y-%m-%d') <= '$dateTo' AND date_format(DateEnd,'%Y-%m-%d') >= '$dateTo')) ORDER BY DateStart, DateEnd;";
    
    
    writeToLog("rewardProgramGet sql: " . $sql);
    
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
