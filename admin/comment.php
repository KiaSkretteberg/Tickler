<?php
	include('../assets/includes/mysql_connect.php');
	session_start();
	if (!isset($_SESSION['user_id'])):
		$loggedIn = 0;
		header('Location: ../index.php');
	else: //benefits if the use is logged in
		$loggedInUser = $_SESSION['user_id'];
		$loggedIn = 1;
		$activeUser = $_SESSION['active'];
	endif;
	$image_id = $_POST['image_id'];
	$comment = $_POST['comment'];
	if($image_id != "" && $comment != ""):
		$result = mysqli_query($con,"INSERT INTO mug_comments (comment, commentor_id, image_id ) VALUES ('$comment','$loggedInUser','$image_id')");
		if (!$result):
			die('Invalid query: ' . mysql_error());
		else:
			$result2 = mysqli_query($con,"SELECT * FROM mug_comments JOIN mug_users ON mug_comments.commentor_id = mug_users.user_id");
			while ($row = mysqli_fetch_array($result2)):
				$comment_id = $row['cid'];
				$commentor_name = $row['first_name']." ".$row['last_name'];
				$taskArray = array('comment' => $comment, 'image_id' => $image_id, 'commentor_id' => $loggedInUser, 'commentor_name' =>$commentor_name, 'comment_id' => $comment_id);
			endwhile;
	        //convert array into json
	        $jsn = json_encode($taskArray);
	        //return json data
	        print_r($jsn);
			
		endif;
	endif;
	

?>