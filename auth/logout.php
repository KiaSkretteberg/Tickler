<?php
session_start();
// remove all session variables
session_unset('dsahkjdhsajkhdaskjhdkjashdkjas'); 
// destroy the session 
session_destroy();
header('HTTP/1.1 200 Success');
echo "Logged Out";