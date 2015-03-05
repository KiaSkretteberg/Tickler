<?php
	$con = mysqli_connect("localhost", "kskretteberg1", "jalh0p311013", "kskretteberg1"); //  mysqli_connect(location of server, username, password, name of database)
	if(mysqli_connect_errno()){
		echo "Failed to connect to mySQL: " . mysqli_connect_errno();
	}
	//This stops SQL Injection in POST vars
	if(!is_array($_POST)){
		foreach ($_POST as $key => $value) { 
			$_POST[$key] = mysqli_real_escape_string($con,$value); 
		} 
	}
	//This stops SQL Injection in GET vars
	foreach($_GET as $key => $value){
		if(is_array($_GET[$key])){
			foreach ($_GET[$key] as $idx => $value) { 
				$_GET[$key][$value] = mysqli_real_escape_string($con,$value); 
			}
		}else {
			$_GET[$key] = mysqli_real_escape_string($con,$value); 
		}
	}
	
?>