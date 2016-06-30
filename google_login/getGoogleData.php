<?php
include 'config/functions.php';

session_start();

if (!empty($_GET['openid_ext1_value_firstname']) && !empty($_GET['openid_ext1_value_lastname']) && !empty($_GET['openid_ext1_value_email'])) {    
    $username = $_GET['openid_ext1_value_firstname'] . $_GET['openid_ext1_value_lastname'];
    $email = $_GET['openid_ext1_value_email'];

    $user = new User();
    if(!empty($email)) {
        session_start();
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['oauth_provider'] = 'Google';
        // ニックネームの登録へ
        header("Location: /nerar/personal.php");

    } else {
        // Something's missing, go back to square 1
        header('Location: error.php');
    }

}
?>
