<?php 
	session_start();
	if (!isset($_SESSION['user_id'])) {
		$loggedIn = 0;
	}else { //benefits if the use is logged in
		$loggedInUser = $_SESSION['user_id'];
		$loggedIn = 1;
		$activeUser = $_SESSION['active'];
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Tickler</title>
	<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="assets/css/styles.css">
	<link rel="stylesheet" type="text/css" href="assets/js/jquery/plugins/nouislider/jquery.nouislider.min.css">
	<link rel="stylesheet" type="text/css" href="assets/vendor/semanticui/semantic.min.css">
	<script src='assets/js/jquery/jquery-1.11.1.min.js'></script>
	<script>
	$(function(){
		$('.comment-form-area').hide();
		// $('.comment-area').hide();
		$('.comment-link').click(function(){
			var thisCommentForm = "#" + ($(this).attr('data-x')).replace('comment-', '');
			$(thisCommentForm).toggle();
		});// close comment-link handler
		$('.comment-form').submit(function(){
			var imageID = $(this).attr('data-x');
			var comment = $('#text-' + imageID).val();
			console.log(comment + " | " + imageID);

			//ajax function to send into to smart page
			$.ajax({
				type: 'POST',
				url: 'admin/comment.php',
				data: {
					'comment': comment,
					'image_id': imageID
				},
				success: function(response){
					$('.comment-area .description').text(response.comment);
					$('.comment-area .commentor-name').text(response.commentor_name);
					$('.comment-area').show();
					location.reload(true);
				}
			});







			return false;
		}); // close comment-form submit handler

	});// close self calling function
	</script>
</head>
<body>
<header>
	<div class='container'>
		<div class='brand'>
			<h1><a href='index.php'><i class='fa fa-leaf'></i> Tickler</a></h1>
			<p class='tagline'>The best place to tickle your friends.</p>
		</div>
		<nav>
			<ul>
				<li><a href='index.php'><i class='fa fa-photo'></i> Home</a></li>
				<?php if($loggedIn == 1) : ?>
					<li><a href='admin/insert.php'><i class='fa fa-plus'></i> <i class='fa fa-photo'></i> Add Photo</a></li>
					<li><a href='profile.php'><i class='fa fa-user'></i> Profile</a></li>
					<li><a href='admin/logout.php' id='signout'><i class='fa fa-lock'></i> Logout</a></li>
				<?php else : ?>
					<li><a href='login.php'><i class='fa fa-lock'></i> Login</a></li>
				<?php endif ?>
			</ul>
		</nav>
	</div>
</header>
<main>
	<div class='container clearFix'>

<?php
function makeClickableLinks($text){
	$text = " " . $text; // fixes problem of not linking if no chars before the link
	$text = preg_replace('/(((f|ht){1}(tp|tps):\/\/)[-a-zA-Z0-9@:%_\+.~#?&\/\/=]+)/i', '<a href="\\1" target="_blank">\\1</a>', $text);
	$text = preg_replace('/([[:space:]()[{}])(www.[-a-zA-Z0-9@:%_\+.~#?&\/\/=]+)/i', '\\1<a href="http://\\2" target="_blank">\\2</a>', $text);
	$text = preg_replace('/([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})/i', '<a href="mailto:\\1" target="_blank">\\1</a>', $text);
	return trim($text);
}// end makeClickableLinks\

?>