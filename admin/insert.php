<?php
	include('../assets/includes/mysql_connect.php');
	include('assets/includes/header.php');
	if(isset($_POST['submit'])){
		$title = trim($_POST['title']);
		$description = trim($_POST['description']);
		$description = mysqli_real_escape_string($con,$description);
		$tags = trim($_POST['tags']);
		$tags = mysqli_real_escape_string($con,$tags);
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

		//validate title
        if ($title != "") {
            $title = filter_var($title, FILTER_SANITIZE_STRING);
            if ($title == "" || strlen($title) < 2) {
                $error1 = 'Please enter a valid title.';
                $errors = true;
            }
        } else {
            $error1 = 'Please enter a title.';
        }
        //validate tags
        
        if($tags != ''){ 
        	$tags = filter_var($tags, FILTER_SANITIZE_STRING);
        	if($tags == ""){
        		$error2 = 'Please fill in valid tags, separated by spaces';
	            $errors = true;
        	}
        }
        //validate description
        if($description != ""){
        	$description = filter_var($description, FILTER_SANITIZE_STRING);
	        if($description == '' || strlen($description) < 10){
	            $error3 = 'Please fill in a post at least 10 characters long.';
	            $errors = true;
	        }
        }else if($description == '' || strlen($description) < 10) {
        	$error3 = 'Please fill in a post at least 10 characters long.';
            $errors = true;
        }
        
        //validate file type
        if($_FILES['myfile']['name']){
            if($fileType != "image/jpeg" && $fileType != "image/png"){
                $error4 = "Wrong file type. File must be an image file: either a jpg or png.";
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
                        $error4 = "File is too large. File must be smaller than 10MB";
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
			if(move_uploaded_file($tempName, "../assets/images/galleries/$filename")){
				//create thumbnails
				include("assets/image_crop.php");
				resize_crop_image(100, 100, "../assets/images/galleries/$filename", "$filename", "../assets/images/galleries/thumbs/small/");
				resize_crop_image(150, 150, "../assets/images/galleries/$filename", "$filename", "../assets/images/galleries/thumbs/medium/");
				resize_crop_image(250, 250, "../assets/images/galleries/$filename", "$filename", "../assets/images/galleries/thumbs/large/");
				//create display images
				createDisplayImage($filename, "../assets/images/galleries/$filename", "../assets/images/galleries/display/small/", 700, $fileType);
				createDisplayImage($filename, "../assets/images/galleries/$filename", "../assets/images/galleries/display/medium/", 800, $fileType);
				createDisplayImage($filename, "../assets/images/galleries/$filename", "../assets/images/galleries/display/large/", 900, $fileType);
				mysqli_query($con, "INSERT INTO mug_data (imageURL, title, tags, description, owner_id) VALUES ('assets/images/galleries/$filename','$title', '$tags', '$description', '$loggedInUser')") or die("Error: " . mysqli_error($con));
				$uploadStatus = "<p><strong>$title</strong> was successfully posted. <i class='green check icon'></i></p>";
				$title = $description = "";
			}else{
				$uploadStatus = "<span class='error'>There was an error with uploading your file.</span>";
			}
		}else{
			$uploadStatus = "<span class='error'>There was an error with your input. Please try again.</span>";
		}//close if validate success
	}//close if isset submit
	function createDisplayImage($filename, $file, $folder, $maxWidth, $fileType){
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

		<h2>New Photo</h2>
		<p>Add a new photo with a description.</p>
		<div class='uploadStatus'><?php echo $uploadStatus?></div>
		<form class='input-info ui form' id='postForm' name='postForm' method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>' enctype='multipart/form-data'>
			<p class='required'>
				<label for='title'>Title:</label>
				<input type='text' id='title' name='title' value='<?php echo $_POST['title']?>'  placeholder='Title'>
				<span class='error'><?php echo $error1 ?></span>
			</p>
			<p>
				<label for='tags'>Tags:</label>
				<textarea id='tags' name='tags' rows='4'><?php echo $_POST['tags']?></textarea>
				<span class='error'><?php echo $error2 ?></span>
			</p>
			<p class='required'>
				<label for='description'>Description:</label>
				<textarea id='description' name='description' rows='7'><?php echo $_POST['description']?></textarea>
				<span class='error'><?php echo $error3 ?></span>
			</p>
			<p class='required'>
				<input type='file' name='myfile' id='imageInput' onchange='updateImage()'>
				<span id='currentImage' class='current-image'</span>
				<span class='error'><?php echo $error4 ?></span>
			</p>
			<p class='clearFix submitArea'>
				<input type='submit' id='submit' name='submit' class='ui green button' value='Upload'>
			</p>
		</form>
<?php 
	include('../assets/includes/footer.php');
?>