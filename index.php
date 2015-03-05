<?php
	include('assets/includes/mysql_connect.php');
	include('assets/includes/header.php');
	$user_gallery = $_GET['profile_id'];
	if($user_gallery){
		$queryFilter = "WHERE user_id = $user_gallery";
	}
	$result = mysqli_query($con, "SELECT * FROM mug_users $queryFilter ORDER BY first_name DESC");
	if (!mysqli_num_rows($result)==0):
		while($row = mysqli_fetch_array($result)):
			$user_id = $row['user_id'];
			$posterResult = mysqli_query($con, "SELECT * FROM mug_data WHERE owner_id = $user_id ORDER BY timedate DESC LIMIT 5");
			if (!mysqli_num_rows($posterResult)==0):
				$avatar = $row['avatarImageURL'];
				$avatar = str_replace("avatar", "avatar/profile/large", $avatar);
				$userName = $row['first_name'] . " " . $row['last_name']; 
				$bio = $row['bio'];?>
				<div class='ui items teal message'>
					<div class="item">
						<div class="ui tiny image">
							<img src="<?php echo $avatar ?>">
						</div>
						<div class="content">
							<a class="header" href='profile.php?profile_id=<?php echo $user_id?>'><?php echo $userName?></a>
							<div class="meta">
								<a href='index.php?profile_id=<?php echo $user_id?>'>View Full Gallery</a>
							</div>
							<?php if($bio):?>
								<div class="description"><?php echo $bio?></div>
							<?php endif?>
						</div>
					</div>
				</div>
				<div class="ui four cards">
				<?php while($row = mysqli_fetch_array($posterResult)):
					$title = $row['title'];
					$image_id = $row['image_id'];
					$imagename = $row['imageURL'];
					$thumbnail = str_replace("galleries", "galleries/thumbs/large", $imagename); ?>
					<div class="card">
						<div class="ui medium image">
							<div class="ui corner teal label"><form method='post' action='<?php echo $_SERVER['PHP SELF']?>'><button type='submit' name='favourite-this' class="ui heart rating" data-max-rating="1"><i class='icon <?php echo ($favourited ==1) ? "active" : ""?>'></i></button></form></div>
							<a href='photo.php?image_id=<?php echo $image_id?>'><img src="<?php echo $thumbnail?>"></a>
						</div>
						<div class="extra">
							<div data-rating="<?php echo $rating?>" class="ui large star rating">
								<i class="icon"></i>
								<i class="icon"></i>
								<i class="icon"></i>
								<i class="icon"></i>
								<i class="icon"></i>
							</div>
							<div class='description'>
								<a href='image.php?image_id=<?php echo $image_id?>'> <?php echo $title?></a><br>
								Posted by <a href='profile.php?profile_id=<?php echo $owner_id?>'><?php echo $userName ?></a>
							</div>
						</div>
					</div>
				<?php endwhile //images loop?>
				</div>
			<?php else ://no posts result?>
			<?php endif //check for posts if?>
		<?php endwhile //user loop?>
	<?php else ://no users result?>
		<p>Welcome to Tickler. Tickler is a community for people to share their photos.</p>
		<?php if($loggedIn):?>
			<p>Why not post one of your own?</p>
		<?php else :?>
			<p><a href='signup.php'>Sign up</a> to post your own photos.</p>
		<?php endif ?>
	<?php endif //check for user if?>
</div>
<!-- side bar search area -->
<!-- <div class='sidebar'>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" class=''>
	</form>
</div> -->
<?php include('assets/includes/footer.php') ?>