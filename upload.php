<?php
// Initialize the session
session_start();

// Regenerating session - avoiding session fixation
if (!isset($_SESSION['initiated'])) 
{
    session_regenerate_id();
    $_SESSION['initiated'] = 1;
    
}
//Session timeout
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    // last request was more than 30 minutes ago  
    $_SESSION = array();
    setcookie(session_name(), '', time() - 2592000, '/');
    session_destroy();
    header("location: login.php");
}
$_SESSION['LAST_ACTIVITY'] = time();

//To avoid session hijacking
if ($_SESSION['check'] != hash('ripemd128', $_SERVER['REMOTE_ADDR'] .$_SERVER['HTTP_USER_AGENT'])){
    $_SESSION = array();
    setcookie(session_name(), '', time() - 2592000, '/');
    session_destroy();
    header("location: login.php");
}

// Include config file
require_once "config.php";
$conn = @new mysqli($hn, $un, $pw, $db);
if ($conn->connect_error) die(error());
 
// Define variables and initialize with empty values
$image_err = $text_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    
    // Check if text is empty
    if(empty($_POST["text"])){
        $text_err = "Please provide text.";
        echo $text_err;
    } 
    
    if(isset($_FILES['image'])){
        $countfiles = count($_FILES['image']['name']);
        $name = htmlentities($_FILES['image']['name'][0]);
        for($i=0;$i<$countfiles;$i++)
        {
            $name = htmlentities($_FILES['image']['name'][$i]);
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            if ($name == "")
            {
                $image_err = "Please select atleast one image.";
                echo $image_err;
            }
            //Discard upload if image is not jpeg
            elseif( $ext !== 'jpeg'){
                $image_err =  "'$name' is not an accepted jpeg file"; 
                echo $image_err;

            }
            //Discard file with same name
            elseif(file_exists("uploads/".$name)){
                $image_err =  "Image with '$name' already exists"; 
                echo $image_err;
            }
//The images get added to a directory named uploads which I have created on the server and given appropriate permissions.
//To upload multiple images please shift + select or command + select.
            if(empty($image_err) && empty($text_err))
            {
                move_uploaded_file($_FILES['image']['tmp_name'][$i], "uploads/".$name);
               
            }
        }
//The text gets appended to a file 'data.txt' on the server.
//Data is stored in data.txt in the format 'filename of the image:text'.  
//For multiple images uploaded at a time with a single text, the text gets appended only once in data.txt,
//however both images are uploaded to the uploads directory.
        if(empty($image_err) && empty($text_err))
        {
            $text = mysql_entities_fix_string($conn, $_POST['text']);
            $file = "data.txt";
            file_put_contents($file, $name.':'.$text."\n", FILE_APPEND | LOCK_EX);
            echo "Upload successful!";
        }
    }
    //Closing connection
    $conn->close();
}

//Sanitizing text inputs
function mysql_entities_fix_string($conn, $string) 
{
    return htmlentities(mysql_fix_string($conn, $string));
}
function mysql_fix_string($conn, $string) 
{
    if (get_magic_quotes_gpc()) 
            $string = stripslashes($string);
    return $conn->real_escape_string($string);
}

//Custom error function
function error()
{

        echo <<< _END
We are sorry, but it was not possible to complete
the requested task. 

Please click the back button on your browser
and try again. If you are still having problems,
please <a href="mailto:admin@server.com">email
our administrator</a>. Thank you.
_END;
}


echo <<<_END
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        body{ font: 14px sans-serif; text-align: left; }
    </style>
</head>
<body>
    <div class="page-header">
        <h1>Hi,welcome!</h1>
        <form action="upload.php" method="post" enctype="multipart/form-data"><pre>
        Text <input type="text" name="text">
        <input type="file" name="image[]" multiple>
        <input type="submit" name="submit" value="UPLOAD" />
        </pre>
        </form>
    </div>
    <p>
        <a href="logout.php" class="btn btn-danger">Sign Out of Your Account</a>
    </p>
</body>
</html>
_END;
?>
     