<?php
    $thisFile = $_SERVER['PHP_SELF'];
    $adminSection = "/~kskretteberg1/dmit2503/Lab2/admin";
    $pos = strpos($thisFile, $adminSection);
    if ($pos === false) {
        $upFolder = ""; 
    } else {
        $upFolder = "../"; 
    }
?>

		</div>
	</main>
	<footer>
		<div class='container'>
			<p>All content utilized for academic purposes. <br>Site design by Kia Skretteberg &copy; 2014</p>
		</div>
	</footer>
    <script src='<?php echo $upFolder ?>assets/js/jquery/plugins/liblink/jquery.liblink.js'></script>
    <script src='<?php echo $upFolder ?>assets/js/jquery/plugins/nouislider/jquery.nouislider.min.js'></script>
    <script src="<?php echo $upFolder ?>assets/vendor/semanticui/semantic.min.js"></script>
    <script src='<?php echo $upFolder ?>assets/js/main.js'></script>
	<script src="https://login.persona.org/include.js"></script>
    <script>
        var signinLink = document.getElementById('signin');
    	if (signinLink) {
    	  signinLink.onclick = function() { navigator.id.request(); };
    	}
    	var signoutLink = document.getElementById('signout');
    	if (signoutLink) {
    	  signoutLink.onclick = function() { navigator.id.logout(); };
    	}
        navigator.id.watch({
            loggedInUser: localStorage.getItem('email') || null,
            onlogin: function(assertion) {
            // A user has logged in! Here you need to:
            // 1. Send the assertion to your backend for verification and to create a session.
            // 2. Update your UI.
            $.ajax({ /* <-- This example uses jQuery, but you can use whatever you'd like */
                type: 'POST',
                url: '/~kskretteberg1/dmit2503/Lab2/auth/login', // This is a URL on your website.
                data: {
                    assertion: assertion
                },
                success: function(res, status, xhr) {
                    // track the currently signed in user
                    var response = JSON.parse(res);
                    var email = response["email"];
                    var unregistered = response["unregistered"];
                    localStorage.setItem('email', email);
                    if(unregistered==true){
                        window.location.pathname = "/~kskretteberg1/dmit2503/Lab2/signup.php";
                    }else {
                        window.location.pathname = "/~kskretteberg1/dmit2503/Lab2/index.php";
                    }
                },
                error: function(xhr, status, err) {
                    navigator.id.logout();
                    alert("Login failure: " + err);
                }
            });
          },
          onlogout: function() {
            // A user has logged out! Here you need to:
            // Tear down the user's session by redirecting the user or making a call to your backend.
            // Also, make sure loggedInUser will get set to null on the next page load.
            // (That's a literal JavaScript null. Not false, 0, or undefined. null.)
            $.ajax({
                type: 'POST',
                url: '/~kskretteberg1/dmit2503/Lab2/auth/logout', // This is a URL on your website.
                success: function(res, status, xhr){
                    // clear the currently signed in user
                    localStorage.removeItem('email');
                    window.location.reload(); 
                },
                error: function(xhr, status, err) {
                    alert("Logout failure: " + err); 
                }
            });
          }
        });
        if(window.location.pathname == "/~kskretteberg1/dmit2503/Lab2/signup.php" && !localStorage.getItem('email')){
            navigator.id.request();
        }else if(window.location.pathname == "/~kskretteberg1/dmit2503/Lab2/signup.php" && localStorage.getItem('email')) {
            $.ajax({ /* <-- This example uses jQuery, but you can use whatever you'd like */
                type: 'POST',
                url: '/~kskretteberg1/dmit2503/Lab2/auth/login', // This is a URL on your website.
                data: {
                    email: localStorage.getItem('email')
                },
                success: function(res, status, xhr) {
                    // track the currently signed in user
                    var response = JSON.parse(res);
                    var email = response["email"];
                    var unregistered = response["unregistered"];
                    localStorage.setItem('email', email);
                    if(unregistered==true){
                        window.location.pathname = "/~kskretteberg1/dmit2503/Lab2/signup.php";
                    }else {
                        window.location.pathname = "/~kskretteberg1/dmit2503/Lab2/index.php";
                    }
                },
                error: function(xhr, status, err) {
                    navigator.id.logout();
                    alert("Login failure: " + err);
                }
            });
        }
    </script>
    
</body>
</html>