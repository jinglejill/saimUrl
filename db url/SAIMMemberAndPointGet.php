<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);    
    ini_set("memory_limit","50M");
    writeToLog("file: " . basename(__FILE__));

        
    if (isset ($_POST["searchText"])
        )
    {
        $searchText = $_POST["searchText"];
    }
    else
    {
        $searchText = '';
    }
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    $sql = "select DISTINCT postcustomer.FirstName Name, postcustomer.Telephone PhoneNo, tblCustomerReward.* from (SELECT rewardpoint.CustomerID,sum(Point) PointAllTime, ifnull(tblPointSpent.PointSpent,0) PointSpent, tblPointRemaining.PointRemaining FROM `rewardpoint` LEFT JOIN (SELECT CustomerID,sum(Point) PointSpent FROM `rewardpoint` WHERE Status = -1 GROUP BY CustomerID) tblPointSpent ON rewardpoint.CustomerID = tblPointSpent.CustomerID LEFT JOIN (SELECT CustomerID,sum(Point*Status) PointRemaining FROM `rewardpoint` WHERE 1 GROUP BY CustomerID) tblPointRemaining ON rewardpoint.CustomerID = tblPointRemaining.CustomerID WHERE rewardpoint.Status = 1 GROUP BY rewardpoint.CustomerID)tblCustomerReward LEFT JOIN postcustomer ON tblCustomerReward.CustomerID = postcustomer.CustomerID where (postcustomer.FirstName like '%$searchText%' or postcustomer.Telephone like '%$searchText%') ORDER BY tblCustomerReward.PointAllTime desc limit 8;";
    $sql .= "select sum(PointAllTime)SumPointAllTime, sum(PointSpent)SumPointSpent, sum(PointRemaining)SumPointRemaining, avg(PointAllTime)AvgPointAllTime, avg(PointSpent)AvgPointSpent, avg(PointRemaining)AvgPointRemaining from (SELECT rewardpoint.CustomerID,postcustomer.Telephone,sum(Point) PointAllTime, ifnull(tblPointSpent.PointSpent,0) PointSpent, tblPointRemaining.PointRemaining FROM `rewardpoint` LEFT JOIN (SELECT CustomerID,sum(Point) PointSpent FROM `rewardpoint` WHERE Status = -1 GROUP BY CustomerID) tblPointSpent ON rewardpoint.CustomerID = tblPointSpent.CustomerID LEFT JOIN (SELECT CustomerID,sum(Point*Status) PointRemaining FROM `rewardpoint` WHERE 1 GROUP BY CustomerID) tblPointRemaining ON rewardpoint.CustomerID = tblPointRemaining.CustomerID LEFT JOIN postcustomer ON rewardpoint.CustomerID = postcustomer.CustomerID WHERE rewardpoint.Status = 1 GROUP BY rewardpoint.CustomerID) tblAll;";
    $sql .= "select sum(PointAllTime)SumPointAllTime, sum(PointSpent)SumPointSpent, sum(PointRemaining)SumPointRemaining, avg(PointAllTime)AvgPointAllTime, avg(PointSpent)AvgPointSpent, avg(PointRemaining)AvgPointRemaining from (select * from (select @i:=@i+1 AS iterator, tblAll.* from (SELECT rewardpoint.CustomerID,postcustomer.Telephone,sum(Point) PointAllTime, ifnull(tblPointSpent.PointSpent,0) PointSpent, tblPointRemaining.PointRemaining FROM `rewardpoint` LEFT JOIN (SELECT CustomerID,sum(Point) PointSpent FROM `rewardpoint` WHERE Status = -1 GROUP BY CustomerID) tblPointSpent ON rewardpoint.CustomerID = tblPointSpent.CustomerID LEFT JOIN (SELECT CustomerID,sum(Point*Status) PointRemaining FROM `rewardpoint` WHERE 1 GROUP BY CustomerID) tblPointRemaining ON rewardpoint.CustomerID = tblPointRemaining.CustomerID LEFT JOIN postcustomer ON rewardpoint.CustomerID = postcustomer.CustomerID WHERE rewardpoint.Status = 1 GROUP BY rewardpoint.CustomerID ORDER BY PointAllTime DESC) tblAll, (SELECT @i:=0) AS tblIterator) tblAllRow WHERE iterator <= (select round(count(DISTINCT CustomerID)*0.2) from rewardpoint))tblTop20Percent;";
    writeToLog("memberAndPointGet sql: " . $sql);
    
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
