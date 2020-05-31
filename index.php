<?php
   if (file_exists('data.txt') == false){
       echo "Nothing to display";
       exit();
   }
   //Reading all the data from data.txt into an array.
   $filedata = file('data.txt');
   $assoc_array = array();

   //Storing the data in an associative array.
   foreach($filedata as $line)
 {
     $tmp = explode(":", $line);
     $assoc_array[$tmp[0]] = $tmp[1];
 }
   //Reading images from the uploads directory
   $dirname = "uploads/";
   $images = glob($dirname."*.*");
   foreach($images as $image) {
    $name = trim(explode('/', $image)[1]);
    //Checking if image name is present as key in the associative array.
    // If it's not it means multiple images were uploaded for a text in which case the data.txt has an entry 
    //for only one of the images with the corresponding text. In this case, we display the text only once but 
    //display all images.
    if(isset($assoc_array[$name]))
    {
        echo $assoc_array[$name].'<br/><br/>';
    }
    echo '<img src="'.$image.'" width="300" height="300"/><br /><br />';
} 

?>

