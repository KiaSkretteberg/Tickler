<?php
	include('../assets/includes/mysql_connect.php');
	include('assets/includes/header.php');
	if(!$loggedIn){
		header('Location: ../login.php');
	}
	if(isset($_POST['submit'])){
		$profile_id = $_SESSION['user_id'];
		$errors = "";
		$uploadStatus;

		$filename = $_FILES['myfile']['name'];
		//make file name a web safe name
		$filename = createNewfilename($filename);
		$fileType = $_FILES['myfile']['type'];
		$fileSize = $_FILES['myfile']['size'];
		$tempName = $_FILES['myfile']['tmp_name'];

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
		$ip = 	$_SERVER['REMOTE_ADDR'];// get IP from sender to detect spammers in the future. May return proxy instead of direct computer, but still better than nothing.
		
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

        //validate file type
        if($_FILES['myfile']['name']){
            if($fileType != "image/jpeg" && $fileType != "image/png"){
                $error11 = "Wrong file type. File must be an image file: either a jpg or png.";
                $errors = true;
            }
            //convert file size to a more familiar format
            if($fileSize > 1024){
                $fileSize = $fileSize/1024;
                $fileSizeMeasure = "KB";
                if($fileSize > 1024){
                    $fileSize = $fileSize/1024;
                    $fileSizeMeasure = "MB";
                    if($fileSize > 10){
                        $error11 = "File is too large. File must be smaller than 2MB";
                        $errors = true;
                    }
                //  if($fileSize > 1024){
                //      $fileSize = $fileSize/1024;
                //      $fileSizeMeasure = "GB";
                //      if($fileSize > 1024){
                //          $fileSize = $fileSize/1024;
                //          $fileSizeMeasure = "TB";
                //      }
                //  }
                }
            }else {
                $fileSizeMeasure = "B";
            }
            $fileSize = round($fileSize, 2);
            $fileSize = $fileSize.$fileSizeMeasure;
        }

		if(!$errors){
            if(move_uploaded_file($tempName, "../assets/images/avatar/$filename")){
            	$result = mysqli_query($con, "SELECT * FROM mug_users WHERE user_id = '$profile_id'");
				while($row = mysqli_fetch_array($result)){
					$avatar = $row['avatarImageURL'];
					if($avatar){
						$baseFileName = str_replace("assets/images/avatar/", "", $avatar);
						if(file_exists('../'.$avatar)){
							unlink("../".$avatar);
						}
						if(file_exists('../assets/images/avatar/thumbs/small/'.$baseFileName)){
							unlink('../assets/images/avatar/thumbs/small/'.$baseFileName);
						}
						if(file_exists('../assets/images/avatar/thumbs/medium/'.$baseFileName)){
							unlink('../assets/images/avatar/thumbs/medium/'.$baseFileName);
						}
						if(file_exists('../assets/images/avatar/thumbs/large/'.$baseFileName)){
							unlink('../assets/images/avatar/thumbs/large/'.$baseFileName);
						}
						if(file_exists('../assets/images/avatar/profile/small/'.$baseFileName)){
							unlink('../assets/images/avatar/profile/small/'.$baseFileName);
						}
						if(file_exists('../assets/images/avatar/profile/medium/'.$baseFileName)){
							unlink('../assets/images/avatar/profile/medium/'.$baseFileName);
						}
						if(file_exists('../assets/images/avatar/profile/large/'.$baseFileName)){
							unlink('../assets/images/avatar/profile/large/'.$baseFileName);
						}
					}
				}
				
                //create thumbnails
                include("assets/image_crop.php");
                resize_crop_image(25, 25, "../assets/images/avatar/$filename", "$filename", "../assets/images/avatar/thumbs/small/");
                resize_crop_image(50, 50, "../assets/images/avatar/$filename", "$filename", "../assets/images/avatar/thumbs/medium/");
                resize_crop_image(100, 100, "../assets/images/avatar/$filename", "$filename", "../assets/images/avatar/thumbs/large/");
                //create profile image
                resize_crop_image(200,200, "../assets/images/avatar/$filename", $filename, "../assets/images/avatar/profile/small/");
                resize_crop_image(250, 250, "../assets/images/avatar/$filename", $filename, "../assets/images/avatar/profile/medium/");
                resize_crop_image(300, 300, "../assets/images/avatar/$filename", $filename, "../assets/images/avatar/profile/large/");
                // update the user's photo
                mysqli_query($con, "UPDATE mug_users SET avatarImageURL = 'assets/images/avatar/$filename' WHERE user_id = '$profile_id'") or die("Error: ". mysqli_error($con));
            }else{
                $uploadStatus = "There was an error with uploading your file.";
            }
        }else{
            $uploadStatus = "There was an error with your input. Please try again.";
        }//close if validate success
	}//close if isset submit
	function createProfilePicture($filename, $file, $folder, $maxWidth, $fileType){
	    //get original image size
	    list($width, $height) = getimagesize($file);
	    $ratio = $width/$height;
	    $newHeight = $maxWidth/$ratio;
	    $thumb = imagecreatetruecolor($maxWidth, $newHeight);
	    if($fileType == 'image/jpeg'){
	        $source = imagecreatefromjpeg($file);
	    }else {
	        $source = imagecreatefrompng($file);
	    }

	    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);
	    $newfilename = $folder . $filename;
	    if($fileType == 'image/jpeg'){
	        imagejpeg($thumb, $newfilename, 80);
	    }else {
	        imagepng($thumb, $newfilename, 8);
	    }
	    
	    imagedestroy($thumb);
	    imagedestroy($source);
	}
	function createNewfilename($text){
	    $filePlusExtension = explode(".", $text);
	    $extension = $filePlusExtension[1];
	    $uniqImageID = uniqid();
	    $text = "$uniqImageID.$extension";
	    return $text;
	}
?>
	<script>
	function updateImage()
		{
			var oFReader = new FileReader();
			oFReader.readAsDataURL(document.getElementById("imageInput").files[0]);

			oFReader.onload = function (oFREvent) {
			    document.getElementById("currentImage").style.backgroundImage="url(" + oFREvent.target.result+ ")";
			    document.getElementById("currentImage").className="current-image image-selected";
			};
		}
	</script>

		<h2>Upload New Photo</h2>
		<p>Choose a new photo to use as your avatar on Tickler.</p>
		<div class='uploadStatus'><?php echo $uploadStatus?></div>
		<form class='input-info' id='postForm' name='postForm' method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>' enctype='multipart/form-data'>
			<p class='required'>
				<input type='file' name='myfile' id='imageInput' onchange='updateImage()'>
				<span id='currentImage' class='current-image'</span>
				<span class='error'><?php echo $error11 ?></span>
			</p>
			<p class='clearFix submitArea'>
				<input type='submit' id='submit' name='submit' value='Upload'>
			</p>
		</form>
<?php 
	include('../assets/includes/footer.php');
?>