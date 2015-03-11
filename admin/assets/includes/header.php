<?php 
	session_start();
	if (!isset($_SESSION['user_id'])) {
		$loggedIn = 0;
		header('Location: ../index.php');
	}else { //benefits if the use is logged in
		$loggedInUser = $_SESSION['user_id'];
		$loggedIn = 1;
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Tickler</title>
	<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="../assets/css/styles.css">
	<link rel="stylesheet" type="text/css" href="../assets/js/jquery/plugins/nouislider/jquery.nouislider.min.css">
	<link rel="stylesheet" type="text/css" href="../assets/vendor/semanticui/semantic.min.css">
	<script src='../assets/js/jquery/jquery-1.11.1.min.js'></script>
</head>
<body>
<header>
	<div class='container'>
		<div class='brand'>
			<h1><a href='../index.php'><i class='fa fa-leaf'></i> Tickler</a></h1>
			<p class='tagline'>The best place to tickle your friends.</p>
		</div>
		<nav>
			<ul>
				<li><a href='../index.php'><i class='fa fa-photo'></i> Home</a></li>
				<li><a href='../insert.php'><i class='fa fa-plus'></i> <i class='fa fa-photo'></i> Add Photo</a></li>
				<li><a href='../profile.php'><i class='fa fa-user'></i> Profile</a></li>
				<li><a href='logout.php'><i class='fa fa-lock'></i> Logout</a></li>
			</ul>
		</nav>
	</div>
</header>
<main>
	<div class='container'>

<?php
function makeClickableLinks($text){
	$text = " " . $text; // fixes problem of not linking if no chars before the link
	$text = preg_replace('/(((f|ht){1}(tp|tps):\/\/)[-a-zA-Z0-9@:%_\+.~#?&\/\/=]+)/i', '<a href="\\1" target="_blank">\\1</a>', $text);
	$text = preg_replace('/([[:space:]()[{}])(www.[-a-zA-Z0-9@:%_\+.~#?&\/\/=]+)/i', '\\1<a href="http://\\2" target="_blank">\\2</a>', $text);
	$text = preg_replace('/([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})/i', '<a href="mailto:\\1" target="_blank">\\1</a>', $text);
	return trim($text);
}// end makeClickableLinks

// include 'PasswordLib.phar';
	
?>