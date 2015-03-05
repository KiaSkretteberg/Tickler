<?php
	session_start();
	if(!isset($_SESSION['dsahkjdhsajkhdaskjhdkjashdkjas'])){
		header("Location:../login.php?origin=edit");
	}
	session_id('dsahkjdhsajkhdaskjhdkjashdkjas');
	include('../assets/includes/mysql_connect.php');

	$entry_id = $_GET['entry_id'];
	$origin = $_GET['origin'];
	if($entry_id){
		mysqli_query($con, "DELETE FROM inspiration_catalogue WHERE entry_id = '$entry_id'");
	}
	switch($origin){
		case 'edit.php':
			header('Location: edit.php');
			break;
		case 'index.php':
			header('Location: index.php');
			break;
		default:
			header('Location: edit.php');
			break;
	}
	
?>