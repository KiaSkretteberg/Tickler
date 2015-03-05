<?php
	include('assets/includes/mysql_connect.php');
	include('assets/includes/header.php');
	$curimage_id = $_GET['image_id'];
	if(!$curimage_id){
		header("Location:index.php");
	}
	$result = mysqli_query($con, "SELECT * FROM mug_data JOIN mug_users ON mug_data.owner_id = mug_users.user_id WHERE image_id = $curimage_id");
	while($row = mysqli_fetch_array($result)):
		$title = $row['title'];
		$description = nl2br($row['description']);
		$image = $row['imageURL'];
		$image = str_replace("galleries", "galleries/display/large", $image);
		$description = makeClickableLinks($description);
		$user_id = $row['owner_id'];
		$tags = trim($row['tags']);
		$tags = str_replace("  "," ",$tags);// replace double spaces with single

		$avatar = $row['avatarImageURL'];
		$avatar = str_replace("avatar", "avatar/profile/large", $avatar);
		$userName = $row['first_name'] . " " . $row['last_name']; ?>
		<div class="ui fluid card">
			<div class="ui centered massive image">
				<div class="ui corner teal label"><form method='post' action='<?php echo $_SERVER['PHP SELF']?>'><button type='submit' name='favourite-this' class="ui heart rating" data-max-rating="1"><i class='icon <?php echo ($favourited ==1) ? "active" : ""?>'></i></button></form></div>
				<img src="<?php echo $image?>">
			</div>
			<div class='content'>
				<div class='right floated event'>
					<div class="label">
						<img class='avatar' src="<?php echo $avatar ?>">
						Posted by <a class="user" href='profile.php?profile_id=<?php echo $user_id?>'><?php echo $userName?></a>
					</div>
				</div>
				<div data-rating="<?php echo $rating?>" id='profile-rating' class="ui massive star rating float right">
					<i class="icon"></i>
					<i class="icon"></i>
					<i class="icon"></i>
					<i class="icon"></i>
					<i class="icon"></i>
				</div>
				<div class='header'>
					<a><?php echo $title?></a>
				</div>
			</div>
			<div class="extra">
				<div class='description'>
					<p><?php echo $description?></p>
					<?php if($loggedIn && $activeUser):?>
						<button class='ui button comment-link' data-x='comment-<?php echo $curimage_id?>'>Comment</button>
					<?php elseif($loggedIn && !$activeUser):?>
						<span title='You must be active to comment'><button class='ui disabled button'>Comment</button></span>
					<?php endif;?>
					<a class='ui teal button' href='index.php'>Return to Gallery</a>
				</div>
			</div> <!-- close extra area -->
		</div> <!-- close image card -->
		<?php 
		$commentResult = mysqli_query($con,"SELECT * FROM mug_comments JOIN mug_users ON mug_comments.commentor_id = mug_users.user_id WHERE image_id = $curimage_id");
		if(mysqli_num_rows($commentResult) != 0):?>
			<div class='ui piled segment divided items comment-area'>
			<h3 class='ui header'>Comments</h3>
			<?php while($row = mysqli_fetch_array($commentResult)):
				$comment_id = $row['cid'];
				$commentor_name = $row['first_name']." ".$row['last_name'];
				$comment = $row['comment'];
				$commentor_id =  $row['commentor_id'];
			?>
				<div class='item'>
					<div class='content'>
						<p class='description'><?php echo $comment?></p>
					</div>
					<div class='extra'>
						<span class='right floated'>Posted by: <a class='commentor-name' href='profile.php?profile_id=<?php $commentor_id?>'><?php echo $commentor_name?></a></span>
					</div>
				</div>
			<?php endwhile;?>
			</div>
		<?php endif;?>
		<div class='ui fluid card comment-form-area' id='<?php echo $curimage_id?>'>
			<form class='comment-form ui form content' name='commentForm' data-x='<?php echo $curimage_id?>'>
				<h3 class='header'>Post a Comment</h3>
				<input type='text' class='comment-text' placeholder='Type your comment here...' id='text-<?php echo $curimage_id?>'>
				<input type='submit' class='ui teal button' style='display:none'>
			</form>
		</div>

	<?php endwhile ?>
<!-- side bar search area -->
<!-- <div class='sidebar'>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" class=''>
	</form>
</div> -->

<?php include('assets/includes/footer.php') ?>