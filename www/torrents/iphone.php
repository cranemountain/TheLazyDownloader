<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>MediaMonster Download</title>
	<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;">

	<style type="text/css">
		@import url("/css/iphone.css");
	</style>
	<script type="text/javascript" src="/js/orientation.js"></script>
	<script type="text/javascript">
		window.addEventListener("load", function() { setTimeout(loaded, 100) }, false);
		function loaded() {
			document.getElementById("page_wrapper").style.visibility = "visible";
			window.scrollTo(0, 1); // pan to the bottom, hides the location bar
		}
	</script>
</head>
<?php
if(isset($_POST['submit'])){
    $mysqli = new mysqli("localhost", "db_username","db_password", "rss_download");

	if (mysqli_connect_errno()) {
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}
    
    $type = strtolower($_POST['type']);
    $name = strtolower($_POST['name']);
    $not_in_name = strtolower($_POST['not_in_name']);
    
    $query = "INSERT INTO favorites (name,not_in_name,type) VALUES ('$name','$not_in_name','$type')";
	$result = $mysqli->query($query);
    header('Location: '.'http://home.helror.se/download/iphone/PASSWORD'); 
    exit;
}
?>
<body onorientationchange="updateOrientation();">
	<div id="page_wrapper">
		<div id="content_left">
<?php
$token = $_GET["token"];
if($token == "PASSWORD"){
	$mysqli = new mysqli("localhost", "db_username","db_password", "rss_download");

	if (mysqli_connect_errno()) {
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}
	
	$query = "SELECT * FROM transmission ORDER BY title";
	if ($result = $mysqli->query($query)) {
		while ($obj = $result->fetch_object()) {
                $date = $obj->added;
                $title = ucwords(str_ireplace("_", " ", $obj->title));
				$tracker = $obj->tracker;
				$ratio = $obj->ratio;
				echo "<p style=\"font-size:13px\">$date - $tracker - $ratio<br></p>";
                echo "<p style=\"font-size:13px\">$title<br><hr></p>";
		}
		$result->close();
	}
}
?>
		</div>
		<div id="content_right">
            <form action="/download/iphone/PASSWORD/" method="post">
            <p>Type</p><select name="type">
            <option value="tv">TV</option>
            <option value="mp3">MP3</option>
            <option value="movies">Movies</option>
            </select><br>
            <p>In Name</p><input type="text" name="name" size="25"><br>
            <p>Not In Name<p><input type="text" name="not_in_name" size="25">
            <input type="submit" name="submit" value="Submit"><br><br>
            </form>
		</div>
		<div id="content_normal">
<?php
$token = $_GET["token"];
if($token == "PASSWORD"){
	$mysqli = new mysqli("localhost", "db_username","db_password", "rss_download");

	if (mysqli_connect_errno()) {
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}
	
	$query = "SELECT * FROM completed_downloads ORDER BY id DESC LIMIT 25";
	if ($result = $mysqli->query($query)) {
		while ($obj = $result->fetch_object()) {
                $date = $obj->timestamp;
                $type = strtoupper($obj->type);
                $title = ucwords(str_ireplace("_", " ", $obj->title));
                if(strlen($title) > 45){
                    $title = substr($title, 0,42)."...";
                }
				echo "<p style=\"font-size:13px\">$date - $type<br></p>";
                echo "<p style=\"font-size:13px\">$title<br><hr></p>";
		}
		$result->close();
	}
}
?>
		</div>
	</div>
</body>
</html>
