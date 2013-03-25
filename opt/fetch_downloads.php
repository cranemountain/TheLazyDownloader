<?php
error_reporting(0);
include('rss_db_connect.php');
include('config.inc');
//date_default_timezone_set('Europe/Stockholm');

// fetch rss 
$rss_feed_query = "SELECT * FROM rss_feeds WHERE active = '1' AND leech != '1' ORDER BY last_hit DESC";
$rss_feed_result = mysql_query($rss_feed_query);
while ($rss_feed_rows = mysql_fetch_array($rss_feed_result)){
  	$rss_type = $rss_feed_rows['type'];
  	$rss_address = $rss_feed_rows['address'];
  	$rss_feed_id = $rss_feed_rows['id'];
  	$rss_site = $rss_feed_rows['site'];
	try{
		$sxml = new SimpleXMLElement("compress.zlib://$rss_address", NULL, TRUE);
		foreach ( $sxml->channel->item as $item ) {
			$title = preg_replace('/^(\[.*\]\s)(.*)/', '$2', $item->title);
			$title = preg_replace('/[\._]/',' ',$title);
			$title = mysql_real_escape_string(trim($title));
			$link = $item->link;
			mysql_query("INSERT INTO torrents (feed_id,site,title,link,type) VALUES ('$rss_feed_id','$rss_site','$title','$link','$rss_type')");
			$last_hit = date('Y-m-d H:i:s');
			mysql_query("UPDATE rss_feeds SET last_hit = '$last_hit' WHERE site = '$rss_site'");
		}
	}
	catch(Exception $e)
	{
		echo "Couldn't fetch XML for $rss_site\n";
	}
}

// fetch leech rss 
$rss_feed_query = "SELECT * FROM rss_feeds WHERE active = '1' AND leech = '1' ORDER BY last_hit DESC";
$rss_feed_result = mysql_query($rss_feed_query);
while ($rss_feed_rows = mysql_fetch_array($rss_feed_result)){
  	$rss_type = $rss_feed_rows['type'];
  	$rss_address = $rss_feed_rows['address'];
  	$rss_feed_id = $rss_feed_rows['id'];
  	$rss_site = $rss_feed_rows['site'];
    $rss_leech_category = $rss_feed_rows['leech_category'];
	try{
		$sxml = new SimpleXMLElement("compress.zlib://$rss_address", NULL, TRUE);
		foreach ( $sxml->channel->item as $item ) {   
			$title = preg_replace('/^(\[.*\]\s)(.*)/', '$2', $item->title);
			$title = preg_replace('/[\._]/',' ',$title);
			$title = mysql_real_escape_string(trim($title));  
			$link = $item->link;
			mysql_query("INSERT INTO torrents_leech (feed_id,site,title,link,type,category) VALUES ('$rss_feed_id','$rss_site','$title','$link','$rss_type','$rss_leech_category')");
			$last_hit = date('Y-m-d H:i:s');
			mysql_query("UPDATE rss_feeds SET last_hit = '$last_hit' WHERE site = '$rss_site'");
		}
	}
	catch(Exception $e)
	{
		echo "Couldn't fetch XML for $rss_site\n";
	}
}

$date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')." - $hours_to_keep_links hours"));
mysql_query("DELETE FROM torrents WHERE timestamp < '$date'");
$last_fetch = date('Y-m-d H:i:s');

// insert into downloads
$fav_query = "SELECT * FROM favorites ORDER by id DESC";
$fav_result = mysql_query($fav_query);
while($fav_rows = mysql_fetch_array($fav_result)){
	$fav_id = $fav_rows['id'];
	$favorite_torrent = "";
	$name = $fav_rows['name'];
	$new_title = str_replace(" ", "_", $name);
	$type = $fav_rows['type'];
	$episode_check = $fav_rows['episode_check'];
	$not_in_name = $fav_rows['not_in_name'];
	$cat_row = mysql_fetch_array(mysql_query("SELECT in_name,not_in_name FROM categories WHERE name = '$type'"));
	$cat_in_name = $cat_row["in_name"];
	$cat_not_in_name = $cat_row["not_in_name"];
	if($cat_in_name == "") {
                $in_name = "NULL";
    }
	if($not_in_name == "") {
		$not_in_name = $cat_not_in_name;
	}
	else {
		$not_in_name = $not_in_name.",".$cat_not_in_name;
	}
	if($type == "tv") {
		$search_name = str_replace(" ","%",$name);
		$preg_name = str_replace(" ",".*", $name);
		$query = "SELECT * FROM torrents WHERE title LIKE '%$search_name%' ORDER BY id";
		$result = mysql_query($query);
		while($row = mysql_fetch_array($result)){
			if(delayCheck($row['feed_id'],$row['timestamp']) == "1"){
				$title = $row['title'];
				if(findMatch($cat_in_name,$not_in_name, $title) == 0){
					$link = $row['link'];
					$site = $row['site'];
					$dl_type = $row['type'];
					if(preg_match("/(\d{1,2})[ex](\d{1,2})/i", $title, $matches)){
						$ep = $matches[0];
						$ep = str_ireplace("e","x",$ep);
						$ep_split = preg_split("/x/",$ep);
						if(strlen($ep_split[0]) < 2) { $ep_split[0] = "0".$ep_split[0]; }
						if(strlen($ep_split[1]) < 2) { $ep_split[1] = "0".$ep_split[1]; }
						$ep = $ep_split[0]."x".$ep_split[1];
						$favorite_torrent = strtolower("$new_title"."_"."$ep");
						if($favorite_torrent != ""){
							insert_fav($favorite_torrent,$title,$link,$type,$site,$dl_type,$fav_id);
						}
					}
				}
			}
		}
	}
	elseif($type == "mp3"){
		$search_name = str_replace(" ","%",$name);
		$query = "SELECT * FROM torrents WHERE title LIKE '%$search_name%' ORDER BY id";
		$result = mysql_query($query);
        while($row = mysql_fetch_array($result)){
			if(delayCheck($row['feed_id'],$row['timestamp']) == "1"){
				$title = $row['title'];
				if(findMatch($cat_in_name,$not_in_name, $title) == 0){
					$link = $row['link'];
					$site = $row['site'];
					$dl_type = $row['type'];
					$favorite_torrent = strtolower("$title");
					if($favorite_torrent != ""){
						insert_fav($favorite_torrent,$title,$link,$type,$site,$dl_type,$fav_id);
					}
				}
			}
		}
	}
	elseif($type == "movies"){
		$search_name = str_replace(" ","%",$name);
		$query = "SELECT * FROM torrents WHERE title LIKE '%$search_name%' ORDER BY id";
		$result = mysql_query($query);
		while($row = mysql_fetch_array($result)){
			if(delayCheck($row['feed_id'],$row['timestamp']) == "1"){
				$title = $row['title'];
				if(findMatch($cat_in_name,$not_in_name, $title) == 0){
					$link = $row['link'];
					$site = $row['site'];
					$dl_type = $row['type'];
					$favorite_torrent = strtolower("$title");
					if($favorite_torrent != ""){
						insert_fav($favorite_torrent,$title,$link,$type,$site,$dl_type,$fav_id);
					}
				}
			}
		}
	}
	elseif($type == "audiobooks"){
		$search_name = str_replace(" ","%",$name);
		$query = "SELECT * FROM torrents WHERE title LIKE '%$search_name%' ORDER BY id";
		$result = mysql_query($query);
		while($row = mysql_fetch_array($result)){
			if(delayCheck($row['feed_id'],$row['timestamp']) == "1"){
				$title = $row['title'];
				if(findMatch($cat_in_name,$not_in_name, $title) == 0){
					$link = $row['link'];
					$site = $row['site'];
					$dl_type = $row['type'];
					$favorite_torrent = strtolower("$title");
					if($favorite_torrent != ""){
						insert_fav($favorite_torrent,$title,$link,$type,$site,$dl_type,$fav_id);
					}
				}
			}
		}
	}
	else{
		$search_name = str_replace(" ","%",$name);
		$preg_name = str_replace(" ",".*", $name);
		$query = "SELECT * FROM torrents WHERE title LIKE '%$search_name%' ORDER BY id";
		$result = mysql_query($query);
		while($row = mysql_fetch_array($result)){
			$title = $row['title'];
			if(findMatch($cat_in_name,$not_in_name, $title) == 0){
				$link = $row['link'];
				$site = $row['site'];
				$dl_type = $row['type'];
				preg_match("@(.*$name.+)@i", $title, $matches_a);
				$new_title = $matches_a[0];
				$favorite_torrent = strtolower("$new_title");
				if($favorite_torrent != ""){
					insert_fav($favorite_torrent,$title,$link,$type,$site,$dl_type,$fav_id);
				}
			}
		}
	}
}

// download torrents
$download_query = "SELECT * FROM downloads WHERE torrent_downloaded = '0' ORDER BY id DESC";
$download_result = mysql_query($download_query);
while($download_rows = mysql_fetch_array($download_result)){
	$get_id = $download_rows['id'];
	$get_original_title = $download_rows['original_title'];
	$get_type = $download_rows['type'];
	$get_link = str_replace(" ","%20",$download_rows['link']);
	$get_link_type = $download_rows['link_type'];
	if($get_link_type == "torrent") {
		exec("transmission-remote $transmission_port -n $transmission_user:$transmission_pass -c '$incomplete_path' -a '$get_link' -w '$complete_path/$get_type'");
	}
	elseif($get_link_type == "nzb") {
		//exec("wget -q '$get_link' -O $sabnzbd_autofetch_path/$get_type/$get_original_title.nzb");  
	}
	mysql_query("UPDATE downloads SET torrent_downloaded = '1' WHERE id = '$get_id'");
}

// download leech torrents
$download_query = "SELECT * FROM torrents_leech WHERE torrent_downloaded = '0' ORDER BY id DESC";
$download_result = mysql_query($download_query);
while($download_rows = mysql_fetch_array($download_result)){
	$get_id = $download_rows['id'];
    $get_leech_category = $download_rows['category'];
	$get_link = str_replace(" ","%20",$download_rows['link']);
        
	exec("transmission-remote $transmission_port -n $transmission_user:$transmission_pass -c '$incomplete_path_leech' -a '$get_link' -w '$complete_path_leech/$get_leech_category/'");
	mysql_query("UPDATE torrents_leech SET torrent_downloaded = '1' WHERE id = '$get_id'");
}

// functions
function findMatch($in_name,$not_in_name,$string){
	$title_ok = "0";
	$not_in_words = preg_split('[\,]', $not_in_name);
	foreach($not_in_words as $not_word){
		if(preg_match("/$not_word/i", $string)){
			$title_ok = "-1";
		}
	}
	if($title_ok == "0" && $in_name != "NULL"){
		$title_ok = "-1";
		$in_name_words = preg_split('[\,]', $in_name);
		foreach($in_name_words as $in_word){
			if(preg_match("/$in_word/i", $string)){
				$title_ok = "0";
			}
		}
	}
	return $title_ok;
}

function delayCheck($feed_id,$added){
	$delay_row = mysql_fetch_row(mysql_query("SELECT delay FROM rss_feeds WHERE id = '$feed_id'"));
	$delay = $delay_row[0];
	if($delay > 0) {
		$delay_time = date("Y-m-d H:i:s",strtotime("$added + $delay minutes"));
	}
	else {
		$delay_time = date('Y-m-d H:i:s');
	}
	if($delay_time <= date('Y-m-d H:i:s')) {
		$out_data = "1";
	}
	else{
		$out_data = "0";
	}
	return $out_data;
}

function insert_fav($favorite_torrent,$original_title,$link,$type,$site,$dl_type,$fav_id) {
	$favorite_torrent = mysql_real_escape_string($favorite_torrent);
	$favorite_torrent = str_ireplace(" ","_",$favorite_torrent);
	$favorite_torrent = str_ireplace(".","_",$favorite_torrent);
	$original_title = str_replace(" ","_",$original_title);
	$max_id = mysql_fetch_array(mysql_query("SELECT MAX(id) as id FROM downloads"));
	$id = $max_id['id'] + 1;
	$last_match_time = date('Y-m-d H:i:s');
	mysql_query("INSERT INTO downloads (id,title,original_title,link,type,site,link_type) VALUES ('$id','$favorite_torrent','$original_title','$link','$type','$site','$dl_type')");
    mysql_query("UPDATE favorites SET last_match = '$last_match_time' WHERE id = '$fav_id'");
}
?>