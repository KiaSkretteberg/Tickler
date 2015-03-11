<?php
    session_start();
    if(!isset($_SESSION['dsahkjdhsajkhdaskjhdkjashdkjas'])){
        header("Location:../login.php?origin=edit");
    }
    session_id('dsahkjdhsajkhdaskjhdkjashdkjas');
    include('../assets/includes/mysql_connect.php');
    include('assets/includes/header.php');
    $loggedInUser = $_SESSION['userID'];

    $entry_id = $_GET['entry_id'];
    if(!$entry_id){
        $default = mysqli_query($con, "SELECT * FROM inspiration_catalogue WHERE inspire_user_id=$loggedInUserLIMIT 1");
        if (mysqli_num_rows($default)!=0){
            while($row = mysqli_fetch_array($default)){
                $entry_id = $row['entry_id'];
            }
        }else {
            $entry_id = -1;
        }
    };
    if(isset($_POST['submit'])){
        $title = trim($_POST['title']);
        $url = trim($_POST['url']);
        $website_name_copyright_owner = trim($_POST['website_name_copyright_owner']);
        $user = trim($_POST['user']);
        $year = trim($_POST['year']);
        $tags = trim($_POST['tags']);
        $tags = mysqli_real_escape_string($con,$tags);
        $description = trim($_POST['description']);
        $description = mysqli_real_escape_string($con,$description);
        $medium = $_POST['medium'];
        $categories = $_POST['categories'];
        $complexity = $_POST['complexity'];
        $cur_user_id = $_SESSION['userID'];
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
        // validate URL using filter: URL must have the http://
        if ($url  != "") {
            $url = filter_var($url , FILTER_SANITIZE_URL);
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                $error2 .= "$url is <strong>not</strong> a valid URL.";
                $errors .= true;
            }
        } else {
            $error2 = 'Please enter a source url starting with http://.';
            $errors .= true;
        }
        //validate year
        if($year == "-1"){
            $error3 = "Please choose a year or select N/A if no copyright information is available.";
            $errors = true;
        }else {
             //validate website_name_copyright_owner
            if ($website_name_copyright_owner != "") {
                $website_name_copyright_owner = filter_var($website_name_copyright_owner, FILTER_SANITIZE_STRING);
                if ($website_name_copyright_owner == "" || strlen($website_name_copyright_owner) < 2) {
                    $error4 = 'Please enter a valid website name.';
                    $errors = true;
                }
            } else {
                $error4 = 'Please enter a website name.';
                $errors = true;
            }
            //validate user
            if ($user != "") {
                $user = filter_var($user, FILTER_SANITIZE_STRING);
                if ($user == "" || strlen($user) < 2) {
                    $error5 = 'Please enter a valid user.';
                    $errors = true;
                }
            }
        }
        //validate tags
        if($tags != ''){ 
            $tags = filter_var($tags, FILTER_SANITIZE_STRING);
            if($tags == ""){
                $error9 = 'Please fill in valid tags, separated by spaces';
                $errors = true;
            }
        }
        //validate description
        if($description != ""){
            $description = filter_var($description, FILTER_SANITIZE_STRING);
            if($description == '' || strlen($description) < 10){
                $error10 = 'Please fill in a post at least 10 characters long.';
                $errors = true;
            }
        }else if($description == '' || strlen($description) < 10) {
            $error10 = 'Please fill in a post at least 10 characters long.';
            $errors = true;
        }
        //validate the medium
        if($medium == "website"){
            $website = 1;
            $logo = 0;
            $web_app = 0;
            $code_snippets = 0;
        }elseif($medium == "logo"){
            $website = 0;
            $logo = 1;
            $web_app = 0;
            $code_snippets = 0;
        }elseif($medium == "web_app"){
            $website = 0;
            $logo = 0;
            $web_app = 1;
            $code_snippets = 0;
        }elseif($medium == "code_snippets"){
            $website = 0;
            $logo = 0;
            $web_app = 0;
            $code_snippets = 1;
        }
        //validate file type
        if($_FILES['myfile']['name']){
            if($fileType != "image/jpeg" && $fileType != "image/png"){
                $error7 = "Wrong file type. File must be an image file: either a jpg or png.";
                $errors .= true;
            }
            //convert file size to a more familiar format
            if($fileSize > 1024){
                $fileSize = $fileSize/1024;
                $fileSizeMeasure = "KB";
                if($fileSize > 1024){
                    $fileSize = $fileSize/1024;
                    $fileSizeMeasure = "MB";
                    if($fileSize > 10){
                        $error7 = "File is too large. File must be smaller than 2MB";
                        $errors .= true;
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
            if($_FILES['myfile']['name']){
                if(move_uploaded_file($tempName, "../assets/images/originals/$filename")){
                    //create thumbnails
                    include("assets/image_crop.php");
                    resize_crop_image(100, 75, "../assets/images/originals/$filename", "$filename", "../assets/images/thumbs100/");
                    resize_crop_image(150, 113, "../assets/images/originals/$filename", "$filename", "../assets/images/thumbs150/");
                    resize_crop_image(250, 188, "../assets/images/originals/$filename", "$filename", "../assets/images/thumbs250/");
                    //create display images
                    createDisplayImage($filename, "../assets/images/originals/$filename", "../assets/images/display700/", 700, $fileType);
                    createDisplayImage($filename, "../assets/images/originals/$filename", "../assets/images/display800/", 800, $fileType);
                    createDisplayImage($filename, "../assets/images/originals/$filename", "../assets/images/display900/", 900, $fileType);
                    mysqli_query($con, "UPDATE inspiration_catalogue SET image='assets/images/originals/$filename', title='$title', url='$url', website_name_copyright_owner='$website_name_copyright_owner', user='$user', year='$year', description='$description', website='$website', logo='$logo', web_app='$web_app', code_snippets='$code_snippets', categories='$categories', complexity='$complexity', inspire_user_id='$cur_user_id' WHERE entry_id='$entry_id'");
                    $uploadStatus = "<p>$title was successfully posted.</p>";
                    $title = $description = "";
                }else{
                    $uploadStatus = "There was an error with uploading your file.";
                }
            }else {
                mysqli_query($con, "UPDATE inspiration_catalogue SET title='$title', url='$url', website_name_copyright_owner='$website_name_copyright_owner', user='$user', year='$year', description='$description', website='$website', logo='$logo', web_app='$web_app', code_snippets='$code_snippets', categories='$categories', complexity='$complexity', inspire_user_id='$cur_user_id' WHERE entry_id='$entry_id'");
                $uploadStatus = "<p>$title was successfully updated.</p>";
                $title = $description = "";
            }
        }else{
            $uploadStatus = "There was an error with your input. Please try again.";
        }//close if validate success
    }//close if isset submit


    //get the specific image and prepopulate the fields
    $result2 = mysqli_query($con, "SELECT * FROM inspiration_catalogue WHERE entry_id=$entry_id");
    if ($entry_id != -1){
        while($row = mysqli_fetch_array($result2)){
            $title = $row['title'];
            $url = $row['url'];
            $website_name_copyright_owner = $row['website_name_copyright_owner'];
            $user = $row['user'];
            $year = $row['year'];
            $description = $row['description'];
            $tags = $row['tags'];
            $website = $row['website'];
            $web_app = $row['web_app'];
            $logo = $row['logo'];
            $code_snippets = $row['code_snippets'];
            $categories = $row['categories'];
            $complexity = $row['complexity'];
            $currentfilename = $row['image'];
            
            $thumbnail = str_replace("originals", "thumbs250", $currentfilename);
        }
    }
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
        function go()
        {
            // box = document.navform.entryselect; // gets the form element by the name attribute
            box = document.getElementById('entryselect'); // gets form element by the id.
            destination = box.options[box.selectedIndex].value;
            if (destination) location.href = "edit.php?entry_id=" + destination;
        }
        function updateImage()
        {
            var oFReader = new FileReader();
            oFReader.readAsDataURL(document.getElementById("imageInput").files[0]);

            oFReader.onload = function (oFREvent) {
                document.getElementById("currentImage").style.backgroundImage="url(" + oFREvent.target.result+ ")";;
            };
        }
    </script>
    <h2>Edit Inspiration Piece</h2>
    <p>Choose a piece to edit from the drop down, make any desired changes, and then press save to update those changes.</p>
    <div class='uploadStatus'><?php echo $uploadStatus?></div>
    <ul class='errors'><?php echo $errors?></ul>
    <form class='input-info' id='postForm' name='postForm' method='post' action='<?php echo $_SERVER['PHP_SELF']."?entry_id=$entry_id"; ?>' enctype='multipart/form-data'>
        <p><?php
            $resultSelect = mysqli_query($con, "SELECT * FROM inspiration_catalogue WHERE inspire_user_id=$loggedInUser");
            if (mysqli_num_rows($resultSelect)!=0){
                echo "\n<label>Choose Post</label>";
                echo "\n<select name='entryselect' id='entryselect' onchange='go()'' >\n";
                while($row = mysqli_fetch_array( $resultSelect )) {
                    $curtitle = $row['title'];
                    $curentry_id = $row['entry_id'];
                    if($curentry_id == $entry_id){
                        echo "<option value='$curentry_id' selected>$title</option>";
                    }else {
                        echo "<option value='$curentry_id'>$curtitle</option>";
                    }
                }
                echo "\n</select>";
            }else {
                echo "You have not submitted any entries yet, please <a href='insert.php'>add</a> one first.";
            }
            ?>
        </p>
        <p>
            <label for='title'>Title:</label>
            <input type='text' id='title' name='title' value='<?php echo $title?>'  placeholder='Title' <?php echo ($entry_id == -1)?'disabled':'' ?>>
            <span class='error'><?php echo $error1 ?></span>
        </p>
        <p>
            <label for='url'>Source Url:</label>
            <input type='url' id='url' name='url' value='<?php echo $url?>' placeholder='http://www.example.com' <?php echo ($entry_id == -1)?'disabled':'' ?>>
            <span class='error'><?php echo $error2 ?></span>
        </p>
        <div><label class='form-heading'>Copyright Information</label></div>
        <p>
            <label for='year'>Copyright Year or Year Posted:</label>
            <select name='year' <?php echo ($entry_id == -1)?'disabled':'' ?>>
                    <option selected disabled value='-1'>--Choose a Copyright Year--</option>
                    <option value='2014' <?php echo ($year == '2014')?'selected':''?>>2014</option>
                    <option value='2013' <?php echo ($year == '2013')?'selected':''?>>2013</option>
                    <option value='2012' <?php echo ($year == '2012')?'selected':''?>>2012</option>
                    <option value='2011' <?php echo ($year == '2011')?'selected':''?>>2011</option>
                    <option value='2010' <?php echo ($year == '2010')?'selected':''?>>2010</option>
                    <option value='2009' <?php echo ($year == '2009')?'selected':''?>>2009</option>
                    <option value='2008' <?php echo ($year == '2008')?'selected':''?>>2008</option>
                    <option value='2007' <?php echo ($year == '2007')?'selected':''?>>2007</option>   
                    <option value='2006' <?php echo ($year == '2006')?'selected':''?>>2006</option>   
                    <option value='2005' <?php echo ($year == '2005')?'selected':''?>>2005</option>   
                    <option value='2004' <?php echo ($year == '2004')?'selected':''?>>2004</option>   
                    <option value='2003' <?php echo ($year == '2003')?'selected':''?>>2003</option>   
                    <option value='2002' <?php echo ($year == '2002')?'selected':''?>>2002</option>   
                    <option value='2001' <?php echo ($year == '2001')?'selected':''?>>2001</option>
                    <option value='2000' <?php echo ($year == '2000')?'selected':''?>>2000</option>
                    <option value='N/A' <?php echo ($year == 'N/A')?'selected':''?>>N/A</option>              
                </select>
            <span class='error'><?php echo $error3 ?></span>
        </p>
        <p>
            <label for='website_name_copyright_owner'>Website Name:</label>
            <input type='text' id='website_name_copyright_owner' name='website_name_copyright_owner' value='<?php echo $website_name_copyright_owner?>'  placeholder='Website Name' <?php echo ($entry_id == -1)?'disabled':'' ?>>
            <span class='error'><?php echo $error4 ?></span>
        </p>
        <p>
            <label for='user'>Posted By User(logos only):</label>
            <input type='text' id='user' name='user' value='<?php echo $user?>'  placeholder='ex. XxUserxX, User12345' <?php echo ($entry_id == -1)?'disabled':'' ?>>
            <span class='error'><?php echo $error5 ?></span>
        </p>
        
        <div>
            <label for='medium'>Inspiration Medium</label>
            <ul class='medium'>
                <li><input type="radio" name="medium" value="website" <?php echo ($medium=='website' || $website == 1)?'checked':'' ?> <?php echo ($entry_id == -1)?'disabled':'' ?>>Website</li>
                <li><input type="radio" name="medium" value="logo" <?php echo ($medium=='logo' || $logo == 1)?'checked':'' ?> <?php echo ($entry_id == -1)?'disabled':'' ?>>Logo</li>
                <li><input type="radio" name="medium" value="web_app" <?php echo ($medium=='web_app' || $web_app == 1)?'checked':'' ?> <?php echo ($entry_id == -1)?'disabled':'' ?>>Web App</li>
                <li><input type="radio" name="medium" value="code_snippets" <?php echo ($medium=='code_snippets' || $code_snippets == 1)?'checked':'' ?> <?php echo ($entry_id == -1)?'disabled':'' ?>>Code Snippets</li>
            </ul>
        </div>
        <p>
            <label for='categories'>Inspiration Category</label>
            <select name='categories' <?php echo ($entry_id == -1)?'disabled':'' ?>>
                <option selected disabled value='-1'>--Choose a Category--</option>
                <option value='Colours' <?php echo ($categories == 'Colours')?'selected':''?>>Colours</option>
                <option value='Effects' <?php echo ($categories == 'Effects')?'selected':''?>>Effects</option>
                <option value='Font' <?php echo ($categories == 'Font')?'selected':''?>>Font</option>
                <option value='General Design' <?php echo ($categories == 'General Design')?'selected':''?>>General Design</option>
                <option value='Icons' <?php echo ($categories == 'Icons')?'selected':''?>>Icons</option>
                <option value='Layout' <?php echo ($categories == 'Layout')?'selected':''?>>Layout</option>
                <option value='Theme' <?php echo ($categories == 'Theme')?'selected':''?>>Theme (ex. playful, stiff, etc)</option>
                <option value='User Interface' <?php echo ($categories == 'User Interface')?'selected':''?>>User Interface</option> 
            </select>
        </p>
        <div>
            <label for='complexity'>Level of Complexity</label>
            <ul class='complexity'>
                <li><input type="radio" name="complexity" value="1" <?php echo ($complexity=='1')?'checked':'' ?> <?php echo ($entry_id == -1)?'disabled':'' ?>>Simple</li>
                <li><input type="radio" name="complexity" value="6" <?php echo ($complexity=='6')?'checked':'' ?> <?php echo ($entry_id == -1)?'disabled':'' ?>>Average</li>
                <li><input type="radio" name="complexity" value="11" <?php echo ($complexity=='11')?'checked':'' ?> <?php echo ($entry_id == -1)?'disabled':'' ?>>Complex</li>
                <li><input type="radio" name="complexity" value="16" <?php echo ($complexity=='16')?'checked':'' ?> <?php echo ($entry_id == -1)?'disabled':'' ?>>Chaotic</li>
            </ul>
        </div>
        <p>
            <label for='tags'>Tags:</label>
            <textarea id='tags' name='tags' rows='4'><?php echo $tags?></textarea>
            <span class='error'><?php echo $error6 ?></span>
        </p>
        <p>
            <label for='description'>Description:</label>
            <textarea id='description' name='description' rows='7' <?php echo ($entry_id == -1)?'disabled':'' ?>><?php echo $description?></textarea>
            <span class='error'><?php echo $error7 ?></span>
        </p>
        <p>
        <label for='description'>Current Image:</label>
            <span id='currentImage' class='current-image' style='background-image: url("<?php echo '../'.$thumbnail?>");<?php echo ($thumbnail)?'width: 250px; height: 188px;':'' ?>'></span>
        </p>
        <p>
            <label for='file'>Choose a new image?</label>
            <input type='file' name='myfile' <?php echo ($entry_id == -1)?'disabled':'' ?> onchange='updateImage()' id='imageInput'>
            <span class='error'><?php echo $error8 ?></span>
        </p>
        <p class='clearFix submitArea form-footer'>
            <input type='submit' id='submit' name='submit' class='submit' value='Save' <?php echo ($entry_id == -1)?'disabled':'' ?>>
            <?php echo ($entry_id != -1)?"<a href='delete.php?entry_id=$entry_id&origin=edit.php' class='button' onclick=\"return confirm('Are you sure you want to delete this entry?')\"><i class='fa fa-trash'></i> Delete</a>":'' ?>
        </p>
    </form>
<?php 
    include('../assets/includes/footer.php');
?>