<?php
session_start();
// remove all session variables
session_unset('dsahkjdhsajkhdaskjhdkjashdkjas'); 
// destroy the session 
session_destroy('dsahkjdhsajkhdaskjhdkjashdkjas'); 
header('Location: ../index.php');

include('assets/includes/mysql_connect.php');
include('assets/includes/header.php');
include('assets/includes/footer.php'); ?>