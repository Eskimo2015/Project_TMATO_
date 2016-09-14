<?php
//Created by: 	Noah Nathan
//Date:			00/08/2016
//Purpose:  	Register new user and store account details to DB

//Create database from MySQL first

//Step 1:  Read from inputs on Registration page
$fname = $_POST['firstname'];
$lname = $_POST['lastname'];
$dob = $_POST["dob"];
$email = $_POST["email"];
$uname = $_POST["username"];
$pword = $_POST["password"];
/*$pw_hash = "hash_temp";*/

//Step 2:  connect to MySQL and select database in one statement
$connection = mysqli_connect("localhost:3306", "root", "", "tmato_db");

//Step 3:  Run query - check DB for existing account
$result = mysqli_query ($connection , "SELECT User_UName FROM user WHERE 
        User_UName LIKE '{$uname}';");

//Step 4a:  If username already exists then abort registration and print message
if (mysqli_fetch_row($result)) {    
    header("Location: ../registration.php?reg_msg=Your chosen USERNAME is already in use!"
        . "  Please enter another USERNAME to create a new account...");
//Step 4b:  Run query - insert form data into user table and print confirmation message
} else { 
    mysqli_query($connection, "INSERT INTO user values(NULL,'{$fname}','{$lname}','{$dob}','{$email}',
    	'{$uname}','{$pword}','',CURDATE(),NULL,'0','0')");
    header("Location: ../registration.php?reg_msg=Your account has been created successfully!");
}

//Step 5:  Close connection
mysqli_close($connection);
?>