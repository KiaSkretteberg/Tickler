<?php
    include('assets/includes/mysql_connect.php');
    include('assets/includes/header.php');
    $profile_id = $_GET['profile_id'];
    if(!$profile_id && !$loggedIn){
        header('Location: index.php');
    }elseif(!$profile_id && $loggedIn){
        $profile_id = $loggedInUser;
    }

    $result = mysqli_query($con, "SELECT * FROM mug_users WHERE user_id = '$profile_id'");
    if (!mysqli_num_rows($result)==0){
        while($row = mysqli_fetch_array($result)){
            $firstName = $row['first_name'];
            $lastName = $row['last_name'];
            $bio = $row['bio'];
            $avatar = $row['avatarImageURL'];
            $avatar = str_replace("avatar", "avatar/profile/large", $avatar);
            $active = $row['active'];
            $registrationDate = $row['registrationDate'];
        }
        /*  
            Gather any users that have "tickled" this user
            any users that this user has "tickled"
            as well as any pending "tickles"
        */
        $tickleResult = mysqli_query($con, "SELECT * FROM mug_tickles JOIN mug_users ON mug_tickles.sender_id = mug_users.user_id WHERE receiver_id = '$profile_id' OR sender_id = '$profile_id' LIMIT 3");
        $tickledByNum = 0;
        $tickledNum = 0;
        $accepted = null;
        $pendingTickles = array();
        if (!mysqli_num_rows($tickleResult)==0){
            while($row = mysqli_fetch_array($tickleResult)){
                $sender_id = $row['sender_id'];
                $sender_name = $row['first_name'] . " " . $row['last_name'];
                $sender_avatar = $row['avatarImageURL'];
                $sender_avatar = str_replace("avatar", "avatar/thumbs/large", $sender_avatar);
                $receiver_id = $row['receiver_id'];
                $tickled = $row['tickled'];
                $datetickled = $row['timesent'];
                if($tickled ==1){
                    if($sender_id == $profile_id){
                        $tickledNum += 1;
                    }elseif ($receiver_id = $profile_id){
                        $tickledByNum += 1;
                    }
                }elseif ($receiver_id == $profile_id) {
                    array_unshift($pendingTickles, array("id" => $sender_id, "avatar" => $sender_avatar, "name" => $sender_name,  "date" => $datetickled));
                }
                if($receiver_id == $profile_id && $sender_id == $loggedInUser){
                    if($tickled == 0){
                        $accepted = false;
                    }else {
                        $accepted = true;
                    }
                }
            }
        }
        /*  
            Gather any messages sent to this user
            Check for which are new
        */
        $messageResult = mysqli_query($con, "SELECT * FROM mug_messages JOIN mug_users ON mug_messages.sender_id = mug_users.user_id WHERE receiver_id = '$profile_id' LIMIT 3");
        $newMessages = 0;
        $messageFeed = array();
        if (!mysqli_num_rows($messageResult)==0){
            while($row = mysqli_fetch_array($messageResult)){
                $sender_id = $row['sender_id'];
                $sender_name = $row['first_name'] . " " . $row['last_name'];
                $sender_avatar = $row['avatarImageURL'];
                $sender_avatar = str_replace("avatar", "avatar/thumbs/large", $sender_avatar);
                $read = $row['read'];
                $messagedate = $row['timesent'];
                if($read == 0){
                    $newMessages += 1;
                    if($newMessages <= 5){
                        array_unshift($messageFeed, array("id" => $sender_id, "avatar" => $sender_avatar, "name" => $sender_name,  "date" => $messagedate));
                    }
                }
            }
        }
    }else {
        header('Location: index.php');
    }
    /* Code for adding a new bio */
    if(isset($_POST['add-bio'])){
        $bio = $_POST['new-bio'];

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

        if($bio != ""){
            $bio = filter_var($bio, FILTER_SANITIZE_STRING);
            if($bio == ''){
                $error1 = 'Please fill in a bio.';
                $errors = true;
            }
        }else if($bio == '') {
            $error1 = 'Please fill in a bio';
            $errors = true;
        }
        if(!$errors){
            mysqli_query($con, "UPDATE mug_users SET bio = '$bio' WHERE user_id = '$profile_id'") or die("Error: ". mysqli_error($con));
        }
    }
    /* Code for tickling a user */
    if(isset($_POST['tickle-me'])){
        mysqli_query($con, "INSERT INTO mug_tickles (sender_id, receiver_id) VALUES('$loggedInUser', '$profile_id')") or die("Error: ". mysqli_error($con));
    }
    /*Code for Messaging A User */
    if(isset($_POST['send-message'])){
        $new_message_content = $_POST['message-content'];
        $new_subject = $_POST['subject'];

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
            mysqli_query($con, "INSERT INTO mug_messages (sender_id, receiver_id, message, subject) VALUES('$loggedInUser', '$profile_id', '$new_message_content', '$new_subject')") or die("Error: ". mysqli_error($con));
        }
    }
?>
<div class='profile'>
    <div class="ui card">
        <div class="image dimmable">
            <div class="ui dimmer">
                <div class="content">
                    <div class="center">
                        <?php if($loggedIn && $profile_id == $loggedInUser && $active == 1) : ?><a href='admin/upload.php' class='ui inverted button'>Upload New Photo</a>
                        <?php elseif($loggedIn && $profile_id == $loggedInUser && !$active) : ?><div class="ui inverted button">You Are Not Yet Active</div>
                        <?php elseif($loggedIn && $profile_id != $loggedInUser && $accepted == true ) : ?><div class="ui inverted button">You Already Tickled Me</div><button class='ui inverted button message-me-btn'>Message Me</button>
                        <?php elseif($loggedIn && $profile_id != $loggedInUser && $accepted === false ) : ?><div class="ui inverted button">Tickle Sent</div><button class='ui inverted button message-me-btn'>Message Me</button>
                        <?php elseif($loggedIn && $profile_id != $loggedInUser && $accepted == null ) : ?><form method='post' action='<?php echo $_SERVER['PHP SELF']?>'><button class='ui inverted button' type='submit' name='tickle-me'>Tickle Me!</button><button class='ui inverted button message-me-btn'>Message Me</button></form>
                        <?php endif?>
                    </div>
                </div>
            </div>
            <img class='avatar-profile' src='<?php echo $avatar?>' alt='<?php echo "$firstName $lastName"?>'>
        </div>
        <div class="content">
            <div class="header"><?php echo "$firstName $lastName" ?></div>
            <div class="meta">
                <a class="group"></a>
            </div>
            <div class="description">
                <?php if($bio) :?>
                    <?php echo $bio ?>
                <?php elseif($loggedIn && $profile_id == $loggedInUser && $active == 1 && !$bio) : ?>
                    <form class="ui fluid accordion form" method='post' action='<?php echo $_SERVER['PHP SELF']?>'>
                        <div class="ui header title">
                            <i class="dropdown icon"></i>
                            Add a Bio
                        </div>
                        <div class="content">
                                <div class="field">
                                    <textarea name='new-bio'></textarea>
                                </div>
                                <button class="ui submit button" name='add-bio'>Submit</button>
                        </div>
                    </form>
                <?php elseif($loggedIn && $profile_id == $loggedInUser && $active == 0 && !$bio) : ?>
                    You must be active to add a bio.
                <?php endif?>
            </div>
            <a class="right floated created">Member Since <?php echo date('M j, Y', strtotime($registrationDate)) ?></a>
        </div>
        <div class="extra content">
            <a class="right floated"><i class='fa fa-leaf'></i> Tickling <?php echo $tickledNum?> User<?php if($tickledNum !=1) : ?>s<?php endif ?></a>
            <a class="friends"><i class='fa fa-leaf'></i> Tickled By <?php echo $tickledByNum?> User<?php if($tickledByNum !=1) : ?>s<?php endif ?></a>
        </div>
    </div>
</div>
<!-- message modal -->
<form id='message-me-modal' class="ui basic modal" method='post' action='<?php echo $_SERVER['PHP SELF']?>'>
    <i class="close icon"></i>
    <div class="header">
        Send <?php echo "$firstName $lastName" ?> a Message
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
<!-- side bar area -->
<div class='sidebar'>
    <div class="ui vertical">
        <?php if($loggedInUser == $profile_id) :?>
            <a class='ui teal label' <?php if($newMessages > 0){ echo "href='admin/interactions.php?task=messages&amp;profile_id=$profile_id'";}?>>
                <i class='mail icon'></i><?php echo $newMessages?> New Message<?php if($newMessages !=1) : ?>s<?php endif ?>
            </a>
            <a class='ui red label' <?php if(count($pendingTickles) > 0){ echo "href='admin/interactions.php?task=tickles&amp;profile_id=$profile_id'";}?>>
                <i class='group icon'></i><?php echo count($pendingTickles)?> Tickle Request<?php if(count($pendingTickles) !=1) : ?>s<?php endif ?> Pending
            </a>
        <?php endif ?>
        <h4 class="ui header">
            <a>Feed</a>
        </h4>
        <div class="ui small feed">
        <?php if(count($messageFeed) == 0 && count($pendingTickles) == 0) :?>
            <div class='content'>
                <p>
                    <?php if($loggedInUser == $profile_id) : ?> 
                        You don't have any notifications. Why not interact with someone first?
                    <?php else : ?>
                        <?php echo "$firstName $lastName" ?> doesn't have any notifications. Why not interact with them?
                    <?php endif ?>
                </p>
            </div>
        <?php endif?>

        <?php for($i = 0; $i < count($messageFeed); $i++) :?>
            <?php if(count($messageFeed) != 0) : ?>
                <div class="event">
                    <div class="label">
                        <img src="<?php echo $messageFeed[$i]["avatar"] ?>">
                    </div>
                    <div class="content">
                        <div class="summary">
                            <a class="user" href='profile.php?profile_id=<?php echo $messageFeed[$i]["id"]?>'>
                                <?php echo $messageFeed[$i]["name"] ?>
                            </a> sent <?php if($loggedInUser == $profile_id){ echo "you";} else { echo "$firstName $lastName";} ?> a message on <?php echo $messageFeed[$i]["date"] ?>
                        </div>
                    </div>
                </div>
            <?php endif?>
        <?php endfor?>
        <?php for($i = 0; $i < count($pendingTickles); $i++) :?>
            <?php if(count($pendingTickles) != 0) : ?>
                <div class="event">
                    <div class="label">
                        <img src="<?php echo $pendingTickles[$i]["avatar"] ?>">
                    </div>
                    <div class="content">
                        <div class="summary">
                            <a class="user" href='profile.php?profile_id=<?php echo $pendingTickles[$i]["id"]?>'>
                                <?php echo $pendingTickles[$i]["name"] ?>
                            </a> tickled <a class="user"><?php if($loggedInUser == $profile_id){ echo "you";} else { echo "$firstName $lastName";} ?></a> on <?php echo $pendingTickles[$i]["date"] ?>
                        </div>
                    </div>
                </div>
            <?php endif?>
        <?php endfor?>
        <?php /* ----------------- get gallery images ----*/
        $galleryResult = mysqli_query($con, "SELECT * FROM mug_data WHERE owner_id = $profile_id ORDER BY timedate DESC LIMIT 3");
        if (!mysqli_num_rows($galleryResult)==0): ?>
            <h4 class="ui header">
                <a>Gallery</a> <a class='ui teal label' href='index.php?profile_id=<?php echo $profile_id?>'><i class='photo icon'></i> View Gallery
                </a>
            </h4>
            <div class="ui three cards">
            <?php while($row = mysqli_fetch_array($galleryResult)): 
                $title = $row['title'];
                $image_id = $row['image_id'];
                $imagename = $row['imageURL'];
                $thumbnail = str_replace("galleries", "galleries/thumbs/large", $imagename); ?>
                <div class="card">
                    <div class="ui small image">
                        <div class="ui corner teal label"><form method='post' action='<?php echo $_SERVER['PHP SELF']?>'><button type='submit' name='favourite-this' class="ui heart rating" data-max-rating="1"><i class='icon <?php echo ($favourited ==1) ? "active" : ""?>'></i></button></form></div>
                        <a href='photo.php?image_id=<?php echo $image_id?>'><img src="<?php echo $thumbnail?>"></a>
                    </div>
                    <div class="extra">
                        <div class='description'>
                            <a href='photo.php?image_id=<?php echo $image_id?>'> <?php echo $title?></a>
                        </div>
                    </div>
                </div>
            <?php endwhile //images loop?>
            </div>
        <?php else: ?>
            <?php if($loggedIn && $profile_id == $loggedInUser && $active == 1) : ?>
                <p>You don't have any photos in your gallery. Why not <a href='admin/insert.php'>upload</a> some?</p>
            <?php elseif($loggedIn && $profile_id == $loggedInUser && !$active) : ?>
                <p>You don't have any images in your gallery. You must be active to upload photos.</p>
            <?php elseif($loggedIn && $profile_id != $loggedInUser) : ?>
                <p><?php echo $firstName . " " . $lastName?> does not have any photos uploaded yet.</p>
            <?php endif ?>
        <?php endif?>
       <!-- <div class="ui tiny images">
            <?php for($i = 0; $i < count($gallery); $i++) :?>
                <?php if(count($gallery) != 0) : ?>
                    <img class="ui image" src="<?php echo $gallery['imageURL']?>">
                <?php endif ?>
            <?php endfor ?>
        </div> -->  
    </div>
</div>
<?php include('assets/includes/footer.php') ?>