<?php
if($_SESSION['logged_in'] == '1' && $_SESSION['admin'] == '1'){
    include('db_connect.inc');
	include('config.inc');
	include('functions.php');
	if($_GET["action"] == "getSystemStats"){
		$today = date('Y-m-d 00:00:00', strtotime("- 0 hours"));
		$dfs = decodeSize(disk_free_space("/var/downloads/"));
		$dl_result = $mysqli->query("SELECT id FROM completed_downloads WHERE timestamp >= '$today'");
		$dl_num_rows = $dl_result->num_rows;
		$tm_result = $mysqli->query("SELECT id FROM transmission");
		$tm_num_rows = $tm_result->num_rows;
		if($tm_num_rows > 0) { $_SESSION["torrents_up"] = $tm_num_rows; } else { $tm_num_rows = $_SESSION["torrents_up"]; }
		echo "<button class=\"top-banner\">DL's Today: $dl_num_rows | Torrents Active: $tm_num_rows | $dfs free | Max Torrent Age: $max_torrent_age days</button>";
	}	
	
	if($_GET["action"] == "getLatestDownloads"){
		$days = $_GET["days"];
		if($days == "" || $days > 90){
			$days = 1;
		}
		$show_days = date('Y-m-d H:i:s', strtotime("- $days days"));
		if(isset($_GET["cat"])){
			$type = $_GET["cat"];
			$_SESSION["last_type"] = $type;
		}
		if($type == "all" || $type == "") { $type = "%"; }
		$result = $mysqli->query("SELECT * FROM completed_downloads WHERE type LIKE '$type' AND type != 'mixed' AND timestamp > '$show_days' ORDER BY timestamp DESC");
		echo "<table>";
		echo "<thead>";
		echo "<th width=\"65\">Completed</th>";
		echo "<th width=\"65\">Category</th>";	
		echo "<th>Title</th>";
		echo "</thead>";
		$i = 0;
		while($object = $result->fetch_object()){
			if($i&1){
				$tr_class = "latest2";
			}
			else {
				$tr_class = "latest";
			}
			if($object->leech == "1"){
				$tr_class = "leech";
			}
			$fresh = date('Y-m-d H:i:s', strtotime("- 30 minutes"));
			if($object->timestamp >= $fresh){
				$tr_class = "fresh";
			}
			$added = fDate($object->timestamp,"md H:i");
			$show_title = str_replace(array(".","_")," ", $object->title);
			$type = $object->type;
			echo "<tr class=\"$tr_class\">";
			echo "<td width=\"65\">$added</td>";
			echo "<td>$type</td>";		
			echo "<td>$show_title</td>";
			echo "</tr>";
			$i++;
		}
		echo "</table>";
	}
	
	if($_GET["action"] == "getFavorites"){
		$type = $_GET["cat"];
		$user_settings_type_value = strtolower($type);
		$show_fav_result = $mysqli->query("SELECT * FROM favorites WHERE type = '$type' ORDER BY name");    
		
		echo "<table align=\"center\">";   
		echo "<thead>";
		echo "<th>In Name</th>";
		echo "<th>Not In Name</th>";
		echo "<th width=\"90\">Last Hit</th>";
		echo "<th width=\"40\">Edit</th>";
		echo "</thead>";
		echo "<form id=\"new_fetch\">";
		echo "<tr class=\"latest\">";
		echo "<td>";
		echo "<input type=\"text\" id=\"new_fav_name\" size=\"40\">";
		echo "</td>";
		echo "<td>";        
		echo "<input type=\"text\" id=\"new_fav_not_in_name\" size=\"50\">";
		echo "</td>";
		echo "<td style=\"font-weight: bold;\"id=\"new_fav_category\" width=\"90\">";
		echo "</td>";
		echo "<td width=\"50\" align=\"center\" onclick=\"favAction('add_fav','null')\" onmouseover=\"this.style.cursor='pointer'\"><p style=\"color: black;\">Add</p>";
		echo "</td>";
		echo "</tr>";
		echo "</form>";
		
		
		while($object = $show_fav_result->fetch_object()){
			$id = $object->id;
			$last_match = fDate($object->last_match, "ymd H:i");
			if($last_match == "700101 00:00"){
				$last_match = "Never";
			}
			$name = utf8_encode(ucwords($object->name));
			$not_in_name = utf8_encode($object->not_in_name);
			$type = ucwords($object->type);
			echo "<tr id=\"favRow$id\" class=\"latest\">";
			echo "<td>";
			echo "<input type=\"text\" id=\"name$id\" value=\"$name\" size=\"40\">";
			echo "</td>";
			echo "<td>";        
			echo "<input type=\"text\" id=\"not_in_name$id\" value=\"$not_in_name\" size=\"50\">";
			echo "</td>";
			echo "<td width=\"90\">$last_match";
			echo "</td>";
			echo "<td width=\"50\">";
			echo "<input type=\"hidden\" id=\"system_api_key$id\" value=\"$system_api_key\">";
			echo "<input type=\"hidden\" id=\"id$id\" value=\"$id\">";
			echo "<img src=\"/images/Delete-icon.png\" width=\"15\" height=\"15\" onclick=\"favAction('del_fav','$id')\" onmouseover=\"this.style.cursor='pointer'\"/><img src=\"/images/Save-icon.png\" width=\"15\" height=\"15\" style=\"padding-left:6px;\" onclick=\"favAction('upd_fav','$id')\" onmouseover=\"this.style.cursor='pointer'\"/>";
			echo "</td>";
			echo "</tr>";
		}
		echo "</table>";
	}
	
	if($_GET["action"] == "getTransmission"){
		//show active
		echo "<div id=\"transmission\">";
		$show_act_query = "SELECT * FROM transmission ORDER BY added DESC";
		$show_act_result = $mysqli->query($show_act_query);
		echo "<table align=\"center\">";
		echo "<thead>";
		echo "<th width=\"65\">Added</th>";
		echo "<th>Title</th>";
		echo "<th width=\"120\">Tracker</th>";
		echo "<th width=\"35\">Size</th>";
		echo "<th width=\"35\">Diff</th>";
		echo "<th width=\"50\">Done</th>";
		echo "<th width=\"10\">&nbsp;</th>";
		echo "</thead>";
		$i = 0;
		while($object = $show_act_result->fetch_object())
		{
			$id = $object->id;
			$added = fDate($object->added, "md H:i");
			$title = $object->title;
			$tracker = explode(".", $object->tracker);
			$tracker = $tracker[(count($tracker) - 2)].".".$tracker[(count($tracker) - 1)];
			$size = round($object->total_size,0);
			$downloaded = round($object->downloaded,0);
			$uploaded = round($object->uploaded,0);
			$diff = $uploaded - $downloaded;
			if($diff >= 0) {
				$background = "bgcolor=\"#E0FFD6\"";
			} else { $background = ""; }
			$ratio = $object->ratio;
			$percent_done = $object->percent_done;
			if($i&1){
				$tr_class = "latest2";
			}
			else {
				$tr_class = "latest";
			}
			if($object->leech == "1"){
				$tr_class = "leech";
			}
			echo "<tr class=\"$tr_class\" id=\"torrentRow$id\">";      
			echo "<td align=\"left\" width=\"65\">";
			echo "$added";
			echo "</td>";
			echo "<td align=\"left\" id=\"title$id\">";
			echo "$title";
			echo "</td>";
			echo "<td align=\"left\" id=\"tracker$id\">";
			echo "$tracker";
			echo "</td>";		
			echo "<td align=\"right\" width=\"30\">";
			echo "$size";
			echo "</td>";	
			echo "<td $background align=\"right\" width=\"30\">";
			echo "$diff";
			echo "</td>";
			echo "<td align=\"right\" width=\"50\">";
			echo "$percent_done %";
			echo "</td>";
			echo "<td align=\"center\"><img src=\"/images/Delete-icon.png\" width=\"13\" height=\"13\" onclick=\"transmissionAction('delete_torrent','$id')\" onmouseover=\"this.style.cursor='pointer'\"/>";
			echo "<input type=\"hidden\" id=\"system_api_key$id\" value=\"$system_api_key\">";
			echo "</td>";
			echo "</tr>";
			$i++;
		}
		echo "</table>";
		echo "</div>";
	}
	if($_GET["action"] == "getSABnzbd"){
		function getSABnzbdData($url){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 4);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			$result = curl_exec($ch);
			return $result;
		}

		// show queue
		$queue_data = getSABnzbdData("https://localhost:9090/api?mode=queue&output=xml&apikey=$sabnzbd_api_key");
		$queue_xml = simplexml_load_string($queue_data);
		if(!count($queue_xml->slots->slot) < 1){
			echo "<table align=\"center\">";
			echo "<thead>";
			echo "<th>Title</th>";
			echo "<th width=\"120\">Category</th>";
			echo "<th width=\"90\">Percentage</th>";
			echo "</thead>";
			$qi = 0;
			foreach($queue_xml->slots->slot as $queueinfo) {
				if($qi&1){
					$tr_class = "latest2";
				}
				else {
					$tr_class = "latest";
				}
				echo "<tr class=\"$tr_class\">";
				echo "<td>$queueinfo->filename</td>";
				echo "<td>$queueinfo->cat</td>";
				echo "<td>$queueinfo->percentage %</td>";
				echo "</tr>";  
				$qi++;
			}
			echo "</table>";
			echo "<br>";
		}
		// show history
		echo "<table align=\"center\">";
		echo "<thead>";
		echo "<th width=\"65\">Added</th>";    
		echo "<th>Title</th>";
		echo "<th width=\"110\">Category</th>";    
		echo "<th width=\"90\">Status</th>";
		echo "</thead>";
		$history_data = getSABnzbdData("https://localhost:9090/api?mode=history&output=xml&apikey=$sabnzbd_api_key");
		$history_xml = simplexml_load_string($history_data);
		$hi = 0;
		foreach($history_xml->slots->slot as $historyinfo) {
			if($hi&1){
				$tr_class = "latest2";
			}
			else {
				$tr_class = "latest";
			}
			$completed = date('md H:i',trim($historyinfo->completed));
			$category = ucwords($historyinfo->category);
			echo "<tr class=\"$tr_class\">";
			echo "<td>$completed</td>";
			echo "<td>$historyinfo->name</td>";
			echo "<td>$category</td>";        
			echo "<td>$historyinfo->status</td>";    
			echo "</tr>";
			$hi++;
		}
		echo "</table>";
	}
}
?>