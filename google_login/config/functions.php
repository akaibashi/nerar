<?php

require 'dbconfig.php';

class User {

    function checkUserGoogle($oauth_provider, $username, $email)
	{    
        $userstable = USERS_TABLE_NAME;
        $query = mysql_query("SELECT * FROM `$userstable` WHERE email = '$email' and oauth_provider = '$oauth_provider'") or die(mysql_error());
        $result = mysql_fetch_array($query);
        if (!empty($result)) {
            # User is already present
        } else {
            #user not present. Insert a new Record
            echo "INSERT INTO `$userstable` (oauth_provider, username, email) VALUES ('$oauth_provider', '$username', '$email')";
            echo "<br/>INSERT INTO `$userstable` (oauth_provider, username, email) VALUES ('$oauth_provider', '$username', '$email')";
            $query = mysql_query("INSERT INTO `$userstable` (oauth_provider, username, email) VALUES ('$oauth_provider', '$username', '$email')") or die(mysql_error());
            $query = mysql_query("SELECT * FROM `$userstable` WHERE email = '$email' and oauth_provider = '$oauth_provider'");
            $result = mysql_fetch_array($query);
            return $result;
        }
        return $result;
    }

    

}

?>
