<?php

// Include config file
require_once "config.php";
$conn = @new mysqli($hn, $un, $pw, $db);
if ($conn->connect_error) die(error());
 
// Define variables and initialize with empty values
$username_err = $password_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Check if username is empty
    if(empty($_POST["username"])){
        $username_err = "Please enter username.";
        echo $username_err;
    } else{
        $username = mysql_entities_fix_string($conn, $_POST['username']);
    }
    
    // Check if password is empty
    if(empty($_POST["password"])){
        $password_err = "Please enter your password.";
        echo $password_err;
    } else{
        $param_password = mysql_entities_fix_string($conn, $_POST['password']);
    }
    
    // Validate credentials
    if(empty($username_err) && empty($password_err)){
        // Prepare a select statement
        $query = "SELECT * FROM users WHERE username = '$username'";
        $result = $conn->query($query);
        if (!$result) 
            echo "Oops! Something went wrong. Please try again later.";
        elseif ($result->num_rows) 
            {
                    $row = $result->fetch_array(MYSQLI_NUM);
                    $salt1 = $row[2];
                    $salt2 = $row[3];
                      
                    //Computing hash of password with salting.
                    $token = hash('sha3-512', "$salt1$param_password$salt2");
                    if ($token == $row[1]){ 
                        
                        // Password is correct, so start a new session
                        session_start();
                        
                        //Regenerating session - avoiding session fixation
                        if (!isset($_SESSION['initiated'])) 
                        {
                            session_regenerate_id();
                            $_SESSION['initiated'] = 1;

                        }

                        
                        $_SESSION['check'] = hash('ripemd128', $_SERVER['REMOTE_ADDR'] .$_SERVER['HTTP_USER_AGENT']);
                        
                        // Store data in session variables
                        $_SESSION["username"] = $username;
                        

                        // Redirect user to upload page
                        header("location: upload.php");
                    }
                    else 
                        echo("Invalid username/password combination");
            }
        else 
            echo("Invalid username/password combination");
        
            //Closing result
            $result->close();
        
    }
    
    // Close connection
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
    <title>Login</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        body{ font: 14px sans-serif; }
        .wrapper{ width: 350px; padding: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Login</h2>
        <p>Please fill in your credentials to login.</p>
        <form action="login.php" method="post">
            <div>
                <label>Username</label>
                <input type="text" name="username" class="form-control">
                <span class="help-block"></span>
            </div>    
            <div>
                <label>Password</label>
                <input type="password" name="password" class="form-control">
                <span class="help-block"></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login">
            </div>
            <p>Don't have an account? <a href="register.php">Sign up now</a>.</p>
        </form>
    </div>    
</body>
</html>
_END;
?>