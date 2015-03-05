<?php
include('../assets/includes/mysql_connect.php');
include('assets/includes/header.php');

$profile_id = $_GET['profile_id'];
if(!$profile_id && !$loggedIn){
    header('Location: ../index.php');
}elseif(!$profile_id && $loggedIn){
    $profile_id = $loggedInUser;
}
$task = $_GET['task'];

/*Code for replying to a message */
    if(isset($_POST['send-message'])){
        $new_message_content = $_POST['message-content'];
        $new_subject = $_POST['subject'];
        $send_to_id = $_POST['reply_to_id'];
        $reply_to_message_id = $_POST['reply_to_message_id'];

        $badStrings = array("Content-Type:",
        "MIME-Version:",
        "Content-Transfer-Encoding:",
        "bcc:",
        "cc:");
        foreach($_POST as $k => $v){
            foreach($badStrings as $v2){
                if(strpos($v, $v2) !== false){
                    // In case of spam, all actions taken here
                    // another way to redirect; we just use JS
                    echo "<script>document.location =\"http://lingscars.com/\" </script>";
                    exit; // stop all further PHP scripting, so mail will not be sent.
                }
            }
        }
        $ip =   $_SERVER['REMOTE_ADDR'];// get IP from sender to detect spammers in the future. May return proxy instead of direct computer, but still better than nothing.
        
        /* Spammer List: ***********/
        $spams = array (
            "static.16.86.46.78.clients.your-server.de", 
            "87.101.244.8", 
            "144.229.34.5", 
            "89.248.168.70",
            "reserve.cableplus.com.cn",
            "94.102.60.182",
            "194.8.75.145",
            "194.8.75.50",
            "194.8.75.62",
            "194.170.32.252"
        ); // array of evil spammers

        foreach ($spams as $site) {// Redirect known spammers
            $pattern = "/$site/i";
            if (preg_match ($pattern, $ip)) {
                // just something to frighten them.
                echo "logging spam activity..";
               echo "<script type=\"text/javascript\">document.location =\"http://www.spamhaus.org/sbl/\" ;</script>"; 
               exit();
            }
        }
        //END SECURITY CHECKS

        //validate subject
        if ($new_subject != "") {
            $new_subject = filter_var($new_subject, FILTER_SANITIZE_STRING);
            if ($new_subject == "" || strlen($new_subject) < 2) {
                $error2 = 'Please enter a valid subject.';
                $errors = true;
            }
        } else {
            $error2 = 'Please enter a subject.';
        }
        //validate message
        if($new_message_content != ""){
            $new_message_content = filter_var($new_message_content, FILTER_SANITIZE_STRING);
            if($new_message_content == ''){
                $error3 = 'Please enter a message';
                $errors = true;
            }
        }else if($new_message_content == '') {
            $error3 = 'Please enter a message';
            $errors = true;
        }
        if(!$errors){
            mysqli_query($con, "INSERT INTO mug_messages (sender_id, receiver_id, message, subject, reply_to_message_id) VALUES('$loggedInUser', '$send_to_id', '$new_message_content', '$new_subject', '$reply_to_message_id')") or die("Error: ". mysqli_error($con));
        }
    }

/* Handle message requests */
if($task == "messages"):
	$messageResult = mysqli_query($con, "SELECT * FROM mug_messages JOIN mug_users ON mug_messages.sender_id = mug_users.user_id WHERE (receiver_id = '$profile_id' AND reply_to_message_id = 0) OR (sender_id = '$profile_id' AND reply_to_message_id = 0) ORDER BY timesent DESC LIMIT 5");
    $newMessages = 0;
    $newMessageFeed = array();
    $oldMessageFeed = array();
    if (mysqli_num_rows($messageResult)!=0): ?>
        <?php while($row = mysqli_fetch_array($messageResult)):
            $sender_id = $row['sender_id']; 
            $receiver_id = $row['receiver_id']?>
            <div class='ui divided items teal message'>
            <?php
        	$message_id = $row['message_id'];
        	$sender_name = $row['first_name'] . " " . $row['last_name'];
            $message = $row['message'];
            $subject = $row['subject'];
            $sender_avatar = $row['avatarImageURL'];
            $sender_avatar = str_replace("avatar", "avatar/profile/small", $sender_avatar);
            $read = $row['read'];
            $messagedate = $row['timesent']; 
            ?>
            <div class="<?php if(!$read) : echo "teal"; endif; ?> item">
				<div class="ui small image">
					<img src="../<?php echo $sender_avatar ?>">
				</div>
				<div class="content">
					<a class="header"><?php echo $subject ?></a>
					<div class="meta">
						<a href='../profile.php?profile_id=<?php echo $sender_id?>'>From: <?php echo $sender_name ?></a>
					    <a>Sent: <?php echo date("M j, Y", strtotime($messagedate)) ?> at <?php echo date("g:i", strtotime($messagedate)) ?></a>
					  </div>
					<div class="description">
						<?php echo $message ?>
					</div>
					<div class="extra">
						<button class='ui right floated teal button reply-message-<?php echo $message_id?>'>
						<i class="reply icon"></i>
						Reply
						
						</button>
					</div>
				</div>
			</div>
			<form id='message-me-modal-<?php echo $message_id?>' class="ui basic modal" method='post' action='<?php echo $_SERVER['PHP SELF']?>'>
			    <i class="close icon"></i>
			    <div class="header">
			        Reply to <?php echo "$sender_name" ?>
			    </div>
			    <div class="content">
			        <div class="image">
			            <i class='mail icon'></i>
			        </div>
			        <div class='ui form'>
			            <div class='field'>
			                <input type='text' name='subject' placeholder='Subject'>
			                <p><?php echo $error2 ?></p>
			            </div>
			            <div class='field'>
				            <input type='hidden' name='reply_to_id' value='<?php echo $sender_id ?>'> 
				            <input type='hidden' name='reply_to_message_id' value='<?php echo $message_id ?>'>
			                <textarea name='message-content' placeholder='Type your message here...'></textarea>
			                <p><?php echo $error3 ?></p>
			            </div>
			        </div>
			    </div>
			    <div class="actions">
			        <div class='two fluid ui inverted buttons'>
			            <div class="ui button basic inverted cancel red">Cancel</div>
			            <button type='submit' class="ui button basic inverted okay green" name='send-message'><i class='send icon'></i> Send</button>
			        </div>
			    </div>
			</form>
			<?php echo "\n<script>
				\n\t$('.reply-message-$message_id').click(function(){
				  	\n\t\t$('#message-me-modal-$message_id').modal('show');
				\n\t})
			\n</script>"; ?>
			<?php $senderResult = mysqli_query($con, "SELECT * FROM mug_messages JOIN mug_users ON mug_messages.sender_id = mug_users.user_id WHERE reply_to_message_id = $message_id ORDER BY timesent ASC");
			if (!mysqli_num_rows($messageResult)==0):
				while($row = mysqli_fetch_array($senderResult)):
		            $newSender = $row['sender_id'];
		            $newReceiver = $row['receiver_id']; 
		            $sender_name = $row['first_name'] . " " . $row['last_name'];
		            $message = $row['message'];
		            $subject = $row['subject'];
		            $sender_avatar = $row['avatarImageURL'];
		            $sender_avatar = str_replace("avatar", "avatar/profile/small", $sender_avatar);
		            $read = $row['read'];
		            $messagedate = $row['timesent']; ?>
	        		<div class="<?php if($read) : echo "teal"; endif; ?> item">
						<div class="ui tiny image">
							<img src="../<?php echo $sender_avatar ?>">
						</div>
						<div class="content">
							<a class="header"><?php echo $subject ?></a>
							<div class="meta">
								<a href='../profile.php?profile_id=<?php echo $sender_id?>'>From: <?php echo $sender_name ?></a>
							    <a>Sent: <?php echo date("M j, Y", strtotime($messagedate)) ?> at <?php echo date("g:i", strtotime($messagedate)) ?></a>
							  </div>
							<div class="description">
								<?php echo $message ?>
							</div>
						</div>
					</div>
				<?php endwhile; //end reply loop?>
			<?php endif; //end replies if?>
			</div> <!--close divided items -->
        <?php endwhile; //end received message loop?>  
    <?php else : header('Location: ../profile.php'); //if no results redirect?>
    <?php endif; //end if table has results?>
<?php endif; //end messages if ?>
<!-- message modal -->

<?php /* Handle tickle requests */




/*accepting / rejecting tickle requests */
if(isset($_POST['reject-tickle'])){
	$current_tickle = $_POST['tickle-id'];
	mysqli_query($con, "DELETE FROM mug_tickles WHERE tickle_id = '$current_tickle'");
}
if(isset($_POST['accept-tickle'])){
	$current_tickle = $_POST['tickle-id'];
	mysqli_query($con, "UPDATE mug_tickles SET tickled=1 WHERE tickle_id = '$current_tickle'");
}



/* Get tickle requests */

if($task == "tickles"):
	$tickleResult = mysqli_query($con, "SELECT * FROM mug_tickles JOIN mug_users ON mug_tickles.sender_id = mug_users.user_id WHERE receiver_id = '$profile_id' ORDER BY timesent DESC");
    if (!mysqli_num_rows($tickleResult)==0):
        while($row = mysqli_fetch_array($tickleResult)): 
            $sender_id = $row['sender_id'];
            $sender_name = $row['first_name'] . " " . $row['last_name'];
            $sender_avatar = $row['avatarImageURL'];
            $sender_avatar = str_replace("avatar", "avatar/thumbs/large", $sender_avatar);
            $tickled = $row['tickled'];
            $datetickled = $row['timesent'];
            $tickle_id = $row['tickle_id'];
            if($tickled != 1): ?>
				<div class="ui teal message">
					<div class="ui tiny image">
						<img src="../<?php echo $sender_avatar ?>">
					</div>
					<div class="content">
						<a class="header" href='../profile.php?profile_id=<?php echo $sender_id?>'><?php echo $sender_name ?></a>
						<div class="description">
							<p><?php echo date("M j, Y", strtotime($datetickled)) ?> at <?php echo date("g:i", strtotime($datetickled)) ?></p>
							<form method='post' method='post' action='<?php echo $_SERVER['PHP SELF']?>'>
								<input type='hidden' name='tickle-id' value='<?php echo $tickle_id ?>'>
								<div class="ui buttons">
									<button class="ui left attached negative button" type='submit' name='reject-tickle'>Reject</button>
									<div class="or"></div>
									<button class="ui right attached positive button" type='submit' name='accept-tickle'>Accept</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			<?php endif; ?>
        <?php endwhile; ?>
    <?php else : header('Location: ../profile.php');?>
    <?php endif; ?>
<?php endif; ?>

<?php include('../assets/includes/footer.php') ?>