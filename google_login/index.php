<?php
//Always place this code at the top of the Page

// セッションの有効期限を1週間に設定
session_cache_expire(10080);

session_start();
if (isset($_SESSION['id'])) {
    // Redirection to login page twitter or facebook
    header("location: home.php");
}

if (array_key_exists("login", $_GET)) {
    $oauth_provider = $_GET['oauth_provider'];
    if ($oauth_provider == 'google') {
        header("Location: login-google.php");
    } 
}
?>
<title>Login</title>
<style type="text/css">
    body{
        background: #f1f1f1;
    }
    #buttons
    {
        text-align:center
    }
    #buttons img,
    #buttons a img
    { border: none;}
    h1
    {
        font-family:Arial, Helvetica, sans-serif;
        color:#999999;
    }

</style>



<div id="buttons">
    <h1>Please authenticate google account.</h1>
    <a href="?login&oauth_provider=google"><img src="images/googlebtn.png"></a><br/>
    <br />
</div>
