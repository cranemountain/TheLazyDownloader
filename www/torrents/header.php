
<head>
<META HTTP-EQUIV="Content-Type" content="text/html"; charset="iso-8859-1" />
<title>The Lazy Downloader</title>
<link rel="stylesheet" type="text/css" href="/css/default.css"/>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/jquery-ui.min.js"></script>
<script src="/js/tools.js"></script>
</head>

<?php
include('functions.php');
$self = $_SERVER['PHP_SELF'];
$ip = $_SERVER['REMOTE_ADDR'];
$date = date('Y-m-d H:i:s');

if(isset($_POST['submit'])){
    $username = $_POST['un'];
    $password = $_POST['up'];
    if($username != "" && $password != ""){
        $user_query = "SELECT * FROM users WHERE username = '$username' AND locked = '0'";
        $user_result = mysql_query($user_query);
        $user_row = mysql_fetch_array($user_result);
        $db_pass = $user_row['password'];
        if($db_pass === $password){
            $db_admin = $user_row['admin'];
            $_SESSION['logged_in'] = '1';
            $_SESSION['admin'] = $db_admin;
            $_SESSION['username'] = $username;
            $_SESSION['password'] = $password;    
            mysql_query("UPDATE users SET last_login_ip = '$ip', last_login_time = '$date' WHERE username = '$username'");
        }
    }
}
if($_SESSION['logged_in'] !== '1'){
    echo "<table class=\"login\" align=\"center\" border=\"0\" cellpadding=\"1\" cellspacing=\"1\">";
    echo "<form enctype=\"multipart/form-data\" action=\"/download/\" method=\"post\" name=\"login\">";
    echo "<tr class=\"style1\">";
    echo "<td align=\"center\">";
    echo "<input type=\"text\" name=\"un\" size=\"12\">"; 
    echo "<input type=\"password\" name=\"up\" size=\"12\">";
    echo "<input type=\"submit\" name=\"submit\" value=\"Login\">";
    echo "</td>";
    echo "</tr>";
    echo "</table>";
    echo "</form>";
}
?>
