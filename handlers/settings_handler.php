<?php
//Created by: 	Noah Nathan
//Date:			15/08/2016
//Purpose:  	Validate Registration Form - fields

include 'handlers/db_conn.php';

//Step 1:  Define variables and set to empty values
$fname = $lname = $dob = $email = $uname = $pword = $bio = "";
$fnameErr = $lnameErr = $dobErr = $emailErr = $unameErr = $pwordErr = $bioErr = ""; 

$regSuccess = "";
$conn_err_msg = "";

$nameMatchExp = "/^[a-zA-Z '-]*$/";
$nameRangeExp = "/^[a-zA-Z '-]{0,32}$/";
$dobMatchExp = "/^(?:(?:19[0-9]{2})|(?:20(?:(?:0[0-9])|(?:1[0-6]))))-(?:(?:02-(?:(?:0[1-9])|(?:1[0-9])|(?:2[0-8])))|(?:(?:(?:0[469])|11)-(?:(?:0[1-9])|(?:[1-2][0-9])|30))|(?:(?:(?:0[13578])|(?:1[02]))-(?:(?:0[1-9])|(?:[1-2][0-9])|3[01])))$/";
$unameMatchExp = "/^\w{3,16}$/";
$pwordMatchExp = "/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)[A-Za-z\d_]{8,16}$/";
$bioMatchExp = "/^[a-zA-Z0-9 '-()]*$/";
$bioRangeExp = "/^[a-zA-Z0-9 '-()]{0,500}$/";

$userID = getUserID();

//DB Connection Check!  If conection problems exist, print error on page.
if (mysqli_connect_errno()) {
	$conn_err_msg = "Unable to connect to database!  " . mysqli_connect_error();
    //$conn_err_msg = die('Connect Error: ' . mysqli_connect_error());
} else {
	//Step 2:  If submission via POST method then validate...
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		if (empty($_POST["firstname"])) {
	    	$fnameErr = "";
	    } else {
			$fname = clean_input($_POST['firstname']);
	  		// check if name only contains letters and whitespace
	  		if (!preg_match($nameMatchExp,$fname)) {
	  			$fnameErr = "Only letters, hyphens(-), apostrophes (') and white space are permitted!";
	  			// check if name exceeds 32 characters
	  		} else if(!preg_match($nameRangeExp,$fname)) {
	  			$fnameErr = "First Name must NOT exceed 32 characters!";
	  		}	
	    }
		if (empty($_POST["lastname"])) {
	    	$lnameErr = "";
	    } else {
			$lname = clean_input($_POST['lastname']);
	  		// check if name only contains letters and whitespace
	  		if (!preg_match($nameMatchExp,$lname)) {
	  			$lnameErr = "Only letters, hyphens(-), apostrophes (') and white space are permitted!";
	  			// check if name exceeds 32 characters
	  		} else if(!preg_match($nameRangeExp,$lname)) {
	  			$lnameErr = "Last Name must NOT exceed 32 characters!";
	  		}	
	    }
		if (empty($_POST["dob"])) {
	    	$dobErr = "";
	    } else {
			$dob = clean_input($_POST["dob"]);
	  		// check if DOB is in correct format yyyy-mm-dd
	  		if (!preg_match($dobMatchExp,$dob)) {
	  			$dobErr = "Date must be in correct format YYYY-MM-DD!  Must be between 1900-01-01 and 2099-12-31.  NOTE: *29 days in February!*";
	  		}	
	    }
	  	if (empty($_POST["email"])) {
	    	$emailErr = "";
	    } else {
			$email = clean_input($_POST["email"]);
	  		//Remove all illegal characters except a-zA-Z0-9!#$%&'*+-/=?^_`{|}~@.[]
	  		$email = filter_var($email, FILTER_SANITIZE_EMAIL);
	  		// check if e-mail address is well-formed
	  		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	  			$emailErr = "Invalid email format!  Must be well formed e.g. contain only ONE '@' and end in '.com' etc.";
	  		}
	    }
	  	if (empty($_POST["password"])) {
	    	$pwordErr = "";
	    } else {
			$pword = clean_input($_POST["password"]);
	  		// check if name only contains letters and whitespace
	  		if (!preg_match($pwordMatchExp,$pword)) {
	  			$pwordErr = "Must contain 8 to 16 characters - at least ONE Uppercase letter, ONE Lowercase letter and ONE Digit!  Must NOT contain white space or special characters except underscores (_).";
	  		}
	    }
	  	if (empty($_POST["bio"])) {
	    	$bioErr = "";
	    } else {
			$bio = clean_input($_POST["bio"]);
		  	//check for valid characters
	  		if (!preg_match($bioMatchExp,$bio)) {
	  			$bioErr = "Only letters, hyphens(-), apostrophes ('), brackets (()) and white space are permitted!";
	  			// check if name exceeds 500 characters
	  		} else if(!preg_match($bioRangeExp,$bio)) {
	  			$bioErr = "Bio must NOT exceed 500 characters!";
	  		}
	    }
	  	
	  	if($fnameErr == "" && $lnameErr == "" && $dobErr == "" && $emailErr == "" && $pwordErr == "" && $bioErr =="") {
	  		$regSuccess = insertUserData($fname, $lname, $dob, $email, $pword, $bio);
	  	}
	}
}

//Trims and cleans input data/strings etc.
function clean_input($data) {
	$data = trim($data);
	$data = htmlspecialchars($data);
	return $data;
}

//Purpose:  Checks for availability of Username
function userNameCheck($uname) {
	$data = "";
	//Step 1:  connect to MySQL and select database
	include 'handlers/db_conn.php';
	//Step 2:  Run query - check DB for existing account
	$result = mysqli_query ($connection , "SELECT User_UName FROM user WHERE
			User_UName LIKE '{$uname}';");
	//Step 3:  If username already exists then create error message
	if (mysqli_fetch_row($result)) {
	  			$data = "The selected USERNAME is unavailable!  Please choose another USERNAME to create an account."; 
  	} else {
  		$data = mysqli_error($connection);
  	}
	//Step 4:  return error message
	return $data;
	//Step 5:  Close connection
	mysqli_close($connection);
}

function insertUserData($fname, $lname, $dob, $email, $pword, $bio) {
	$data = "";
	$userID = getUserID();
	//Step 1:  connect to MySQL and select database
	include 'handlers/db_conn.php';

	//Step 2:  Update user data to User table and print confirmation message
	if (!$connection) {
		$data = "There was an issue storing your details!  " . mysqli_error($connection);
	} else {
		if (!empty($fname)) {
			mysqli_query($connection, "UPDATE user SET User_FName = '$fname' WHERE User_ID = $userID;");
		} else {
			$fnameErr = "NO CHANGES WERE MADE!"; //Feature does not currently work!
		}
		if (!empty($lname)) {
			mysqli_query($connection, "UPDATE user SET User_LName = '$lname' WHERE User_ID = $userID;");
		} else {
			$lnameErr = "NO CHANGES WERE MADE!"; //Feature does not currently work!
		}
		if (!empty($dob)) {
			mysqli_query($connection, "UPDATE user SET User_DOB = '$dob' WHERE User_ID = $userID;");
		} else {
			$dobErr = "NO CHANGES WERE MADE!"; //Feature does not currently work!
		}
		if (!empty($email)) {
			mysqli_query($connection, "UPDATE user SET User_Email = '$email' WHERE User_ID = $userID;");
		} else {
			$emailErr = "NO CHANGES WERE MADE!"; //Feature does not currently work!
		}
		if (!empty($pword)) {
			mysqli_query($connection, "UPDATE user SET User_Password = '$pword' WHERE User_ID = $userID;");
		} else {
			$pwordErr = "NO CHANGES WERE MADE!"; //Feature does not currently work!
		}
		if (!empty($bio)) {
			mysqli_query($connection, "UPDATE user SET User_Bio = '$bio' WHERE User_ID = $userID;");
		} else {
			$bioErr = "NO CHANGES WERE MADE!"; //Feature does not currently work!
		}
		resetFields();
		$data = "Your account has been Updated!";
	} 

	return $data;
	
	//Step 3:  Close connection
	mysqli_close($connection);
}
function resetFields(){
	$_POST["firstname"] = $_POST["lastname"] = $_POST["dob"] = $_POST["email"] = $_POST["password"] = $_POST["bio"] = null;
}

function getUserID(){
	include "db_conn.php";
	
	if (!$connection) {
		echo "<p class='conn_err_msg'>Unable to connect to database!  No data to display.<p>";
	} else {
		$result = mysqli_query($connection, "SELECT User_ID FROM user where User_UName LIKE '{$_SESSION['user']}';");
	}
	while ($data = mysqli_fetch_row($result)){
		return $data[0];
	}
}
?>