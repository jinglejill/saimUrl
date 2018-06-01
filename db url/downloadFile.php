<?php
    $dbName = $_POST['dbName'];
    
    if(isset($_POST['fileName']))
    {
        $fileName = $_POST['fileName'];
    }
    else
    {
        $fileName = "201508131130161.jpg";
    }
    
    
    $b64image = "";
    if($fileName != "")
    {
        $filenameIn  = "./" . $dbName . "/SalesFiles/" . $fileName;
        
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
    

    
    echo json_encode(array('base64String' => $b64image, 'post_image_filename' => $fileName));
    exit();
?>