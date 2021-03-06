<?php
//Created by: 	Noah Nathan
//Date:			01/09/2016
//Purpose:  	User Login - First validates data entry into Login Form then, checks user exists and if TRUE loads account to SESSION.

include 'handlers/db_conn.php';

//Step 1:  Define variables and set to empty values
$uname = $pword = "";
$unameErr = $pwordErr = "";

$loginFail = "";
//$welcomeMsg = "";
$conn_err_msg = "";

$unameMatchExp = "/^\w{3,16}$/";
$pwordMatchExp = "/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)[A-Za-z\d_]{8,16}$/";

//DB Connection Check!  If conection problems exist, print error on page.
if (mysqli_connect_errno()) {
	$conn_err_msg = "Unable to connect to database!  " . mysqli_connect_error();//debugging currently, concattination can be removed if needed
    //$conn_err_msg = die('Connect Error: ' . mysqli_connect_error());
} else {
	//Step 2:  If submission via POST method then validate...
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		if (empty($_POST["username"])) {
			$unameErr = "Username is required!";
		} else {
			$uname = clean_input($_POST["username"]);
			// check if name only contains letters and whitespace
			if (!preg_match($unameMatchExp,$uname)) {
				$unameErr = "Must contain 3 to 16 characters - Must NOT contain white space or special characters except underscores (_).";
			}
		}
		if (empty($_POST["password"])) {
			$pwordErr = "Password is required!";
		} else {
			$pword = clean_input($_POST["password"]);
			// check if name only contains letters and whitespace
			if (!preg_match($pwordMatchExp,$pword)) {
				$pwordErr = "Must contain 8 to 16 characters - at least ONE Uppercase letter, ONE Lowercase letter and ONE Digit!  Must NOT contain white space or special characters except underscores (_).";
			}
		}
		if($unameErr == "" && $pwordErr == "") {
			if(userDetailsCheck($_POST["username"], $_POST["password"])){
				createSession($_POST["username"]);
			} else {
				$loginFail = loginFail();
			}
		}
	}
}

//Trims and cleans input data/strings etc.
function clean_input($data) {
	$data = trim($data);
	//$data = stripslashes($data);
	$data = htmlspecialchars($data);
	return $data;
}

//Purpose:  Checks User is valid
function userDetailsCheck($urname, $pword) {
	//Connect to MySQL and select database
	include 'handlers/db_conn.php';
	//Find match for Username and Password
	$result = mysqli_query($connection, "SELECT * FROM user WHERE
			User_UName LIKE '$urname' AND User_Password LIKE '$pword';");
	//If user exists return true
	if (mysqli_fetch_row($result)) {
		return true;
	} else { //otherwise return false
		return false;
	}
	//Close connection
	mysqli_close($connection);
}

function createSession($urname) {
	session_start();
	$_SESSION["loggedin"] = true;
	$_SESSION["user"] = $urname;
	$_SESSION["uID"] = getID($urname);
	header("Location: user.php?welcome_msg=Welcome, you are ");
}

function loginFail() {
	return "Your login details were NOT recognised!"
			. "  Please re-enter your login details...";
// 	header("Location: login.php?login_fail_msg=Your login details were NOT recognised!"
// 			. "  Please re-enter your login details...");  
}

function getID($uname){
	include "handlers/db_conn.php";
	if (!$connection) {
		return "<p class='conn_err_msg'>Unable to connect to database!  No data to display.<p>";
	} else {
		$result = mysqli_query($connection, "SELECT User_ID FROM user where User_UName LIKE '{$uname}';");
	}
	while ($data = mysqli_fetch_row($result)){
		return $data[0];
	}
}
?>