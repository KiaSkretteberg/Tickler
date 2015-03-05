<?php
require_once "../assets/includes/persona.php";
include('../assets/includes/mysql_connect.php');

$email = NULL;
if (isset($_POST['assertion'])) {
    $persona = new Persona();
    $result = $persona->verifyAssertion($_POST['assertion']);

    if ($result->status === 'okay') {
        $email = $result->email;
        $result = mysqli_query($con, "SELECT * FROM mug_users WHERE email LIKE '$email'");
        if (mysqli_num_rows($result)!=0){
            header('HTTP/1.1 200 Success');
            session_start();
            $_SESSION = mysqli_fetch_array ($result, MYSQLI_ASSOC);
            echo "{\"email\": \"$email\", \"unregistered\": false}";
        }else {
            header('HTTP/1.1 200 User Not Registered');
            session_start();
            $_SESSION['email'] = $email;
            echo "{\"email\": \"$email\", \"unregistered\": true}"; 
        }
    } else {
        header('HTTP/1.1 403 Failure');
        echo "Error: " . $result->reason . "";
    }
}elseif (isset($_POST['email'])){
    $email = $_POST['email'];
    $result2 = mysqli_query($con, "SELECT * FROM mug_users WHERE email LIKE '$email'");
    if (mysqli_num_rows($result2)!=0){
        header('HTTP/1.1 200 Success');
        session_start();
        $_SESSION = mysqli_fetch_array ($result2, MYSQLI_ASSOC);
        echo "{\"email\": \"$email\", \"unregistered\": false}";
    }else {
        header('HTTP/1.1 200 User Not Registered');
        session_start();
        $_SESSION['email'] = $email;
        echo "{\"email\": \"$email\", \"unregistered\": true}"; 
    }
}else {
    echo "No Assertion";
}

?>