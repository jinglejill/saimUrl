<?php
    include_once('./dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    $dbName = $_POST['dbName'];
    if(isset($_POST['imageFileName']))
    {
        $imageFileName = $_POST['imageFileName'];
    }
    else
    {
        $imageFileName = "201508131130161.jpg";
    }
    
    
    $b64image = "";
    if($imageFileName != "")
    {
        $filenameIn  = "./" . $dbName . "/Uploads/" . $imageFileName;
        
        // Check if file already exists
        if (file_exists($filenameIn))
        {
//            echo "file found";
            $b64image = base64_encode(file_get_contents($filenameIn));
            
        }
        else
        {
            
//            echo "download file not found";
        }
    }
    

    
    echo json_encode(array('base64String' => $b64image, 'post_image_filename' => $imageFileName));
    exit();
?>