<?php
// Include config file
require_once "config.php";
$conn = @new mysqli($hn, $un, $pw, $db);
if ($conn->connect_error) die(error());
 
// Define variables and initialize with empty values
$username_err = $password_err =  "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Validate username
    if(empty($_POST["username"])){
        $username_err = "Please enter a username.";
        echo $username_err;
    } else{
        // Prepare a select statement
        $username = mysql_entities_fix_string($conn, $_POST['username']);
        $query = "SELECT * FROM users WHERE username = '$username'";
        $result = $conn->query($query);
        if (!$result) 
            echo "Oops! Something went wrong. Please try again later.";
        else
        {
            if ($result->num_rows)
                $username_err = "This username is already taken.";
                echo $username_err;
        }
        $result->close();
    }
    // Validate password
    if(empty(($_POST["password"]))){
        $password_err = "Please enter a password."; 
        echo $password_err;
    } 
    else{
        $param_password = mysql_entities_fix_string($conn, $_POST['password']);
    }

    
    // Check input errors before inserting in database
    if(empty($username_err) && empty($password_err)){
        $salt1 = random_bytes(5);
        $salt2 = random_bytes(5);
        
        // Prepare an insert statement
        $sql = "INSERT INTO users (username, password, salt1, salt2) VALUES (?, ?,?,?)";
        $password = hash('sha3-512', "$salt1$param_password$salt2");
        
        $stmt = $conn->prepare("INSERT INTO users (username, password, salt1, salt2) VALUES (?, ?,?,?)");
        $stmt->bind_param('ssss', $username, $password,$salt1, $salt2);
        $stmt->execute();

        if (!$stmt) 
            echo "Something went wrong. Please try again later.";
        else
            header("location: login.php");

        //Closing statement
        $stmt -> close();
         
    }
    
    // Close connection
    $conn->close();
}

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
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        body{ font: 14px sans-serif; }
        .wrapper{ width: 350px; padding: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Sign Up</h2>
        <p>Please fill this form to create an account.</p>
        <form action="" method="post">
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
                <input type="submit" class="btn btn-primary" value="Submit">
            </div>
            <p>Already have an account? <a href="login.php">Login here</a>.</p>
        </form>
    </div>  
</body>
</html>
_END;
?>