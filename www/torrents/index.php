<?php
error_reporting(E_ALL);
include('db_connect.inc');
include('config.inc');
include('header.php');
if(($_SESSION['logged_in'] === '1') && ($_SESSION['admin'] === '1'))
{
	echo "<body onload=\"firstLoad()\">";
	echo "<div class=\"top_banner\" id=\"top\">The Lazy Downloader<br><span class=\"menu\" id=\"systemStats\"></span></div>";
	echo "<div class=\"menu\" id=\"menu\">";
	echo "<button style=\"width: 115px;\" onclick='showCat(\"all\")'>Downloads</button><br>
		<button style=\"width: 115px;\" onclick='loadTransmission()'>Transmission</button><br>
		<button style=\"width: 115px;\" onclick='loadSABnzbd()'>SABnzbd</button><br>
		<button style=\"width: 115px;\" onclick='loadAdmin()'>Admin</button><br><hr>";
	echo "<div id=\"sub_menu\">";
	echo "</div>";
	echo"</div menu>";
	echo "<div class=\"main\" id=\"main\">";
	echo "</div main>";
	echo "<input type=\"hidden\" id=\"showing\"></input>";
	echo "<input type=\"hidden\" id=\"show_days\"></input>";
	echo "<input type=\"hidden\" id=\"refresh\"></input>";
	echo "<input type=\"hidden\" id=\"current_page\"></input>";
	echo "<input type=\"hidden\" id=\"system_api_key\" value=\"$system_api_key\"></input>";
	echo "</body>";
}
?>