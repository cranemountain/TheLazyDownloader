<?php
include('db_connect.inc');
include('config.inc');
//functions
function fixValue($indata) {
	$data = trim(preg_replace('/\(.+\)/','', $indata));
	$data = trim(preg_replace('/(MB)/','', $data));
	if(preg_match('/GB/i',$data)){
		$data = trim(str_ireplace("GB","",$data));
		$data = ($data * 1024);
	}
	if(preg_match('/KB/i',$data)){
		$data = trim(str_ireplace("KB","",$data));
		$data = ($data / 1024);
	}
	$data = round($data, 2);
	if($indata == "None") {
		$data = "0.00";
	}
	$data = trim($data);
	if ($data == "") { $data = $indata; }
	return $data;
}

function getPageData($url){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 4);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	$result = curl_exec($ch);
	return $result;
}

//main entrance for API commands
if($_GET["key"] == $system_api_key){
	$id = urldecode($_GET["id"]);
	$action = $_GET["action"];
	
	if($action == "add_fav"){
		$name = utf8_decode($_GET["name"]);
		$not_in_name = $_GET["not_in_name"];
		$category = strtolower($_GET["category"]);
		mysql_query("INSERT INTO favorites (name,not_in_name,type) VALUES ('$name','$not_in_name','$category')"); 
	}
	
	if($action == "del_fav"){
		mysql_query("DELETE FROM favorites WHERE id = '$id'");   
	}
	
	if($action == "upd_fav"){
		$name = strtolower(utf8_decode($_GET["name"]));
		$not_in_name = strtolower(utf8_decode($_GET["not_in_name"]));
		mysql_query("UPDATE favorites SET name='$name', not_in_name='$not_in_name' WHERE id ='$id'");
	}
	
	if($action == "delete_torrent"){
		exec("transmission-remote $transmission_port -n $transmission_user:$transmission_pass -t $id -r");
		mysql_query("DELETE FROM transmission WHERE id = '$id'");
	}
	
	if($action == "upd_var"){
		$variable = mysql_real_escape_string(utf8_decode($_GET["variable"]));
		$data = mysql_real_escape_string(utf8_decode($_GET["data"]));
		mysql_query("UPDATE system_settings SET data='$data' WHERE variable = '$variable'");
	}
	
	if($action == "upd_rss"){
		$active = utf8_decode($_GET["active"]);
		$delay = utf8_decode($_GET["delay"]);
		$type = utf8_decode($_GET["type"]);
		$site = utf8_decode($_GET["site"]);
		$address = utf8_decode($_GET["address"]);
		mysql_query("UPDATE rss_feeds SET active='$active',delay='$delay',type='$type',site='$site',address='$address' WHERE id = '$id'");
	}
	
	if($action == "upd_cat"){
		$name = utf8_decode($_GET["name"]);
		$in_name = utf8_decode($_GET["in_name"]);
		$not_in_name = utf8_decode($_GET["not_in_name"]);
		$episode_check = utf8_decode($_GET["episode_check"]);
		mysql_query("UPDATE categories SET name='$name',in_name='$in_name',not_in_name='$not_in_name',episode_check='$episode_check' WHERE id = '$id'");
	}
	
	//returns XML for last 25 completed downloads
	if($action == "get_rss"){
		$type = $_GET["cat"];
		if($type == "") { $type = "%"; }
		$xmlstr = '<?xml version="1.0" encoding="UTF-8"?>';
		$xmlstr .= "<Response>";
		$xmlstr .= "<title>Latest Downloads</title>";
		$xmlstr .= "<Items>";
		$xmlstr .= "</Items>";
		$xmlstr .=	"</Response>";

		$items = new SimpleXMLElement($xmlstr);
		
		$query = "SELECT * FROM completed_downloads WHERE type LIKE '$type' ORDER BY id DESC LIMIT 25";
		$result = mysql_query($query) or die ("Could not execute query");

		while($row = mysql_fetch_object($result)) {
			$title = ucwords(str_replace("_"," ",$row->title));
			
			$item = $items->Items->addChild('item');
			$item->addChild('id', "$row->id");
			$item->addChild('pubDate', "$row->timestamp");
			$item->addChild('title', "$title");
			$item->addChild('category',"$row->type");
			$item->addChild('downloadType',"$row->download_type"); 
			$item->addChild('description',"$row->id $row->type $row->download_type");    
		}
		header("Content-type:text/xml; charset=utf-8");
		echo $items->asXML();
	}
    
	//returns RSS XML for last 25 collected releases
	if($action == "get_releases_rss"){
		$xmlstr = '<?xml version="1.0" encoding="UTF-8"?>';
		$xmlstr .= "<Response>";
		$xmlstr .= "<title>Latest Releases</title>";
		$xmlstr .= "<Items>";
		$xmlstr .= "</Items>";
		$xmlstr .=	"</Response>";

		$items = new SimpleXMLElement($xmlstr);
		
		$query = "SELECT * FROM torrents GROUP BY title ORDER BY id DESC LIMIT 25";
		$result = mysql_query($query) or die ("Could not execute query");

		while($row = mysql_fetch_object($result)) {
			$title = htmlspecialchars(ucwords(str_replace("_"," ",$row->title)));
			$item = $items->Items->addChild('item');
			$item->addChild('id', "$row->id");
			$item->addChild('title', "$title");
			$item->addChild('site', "$row->site"); 
			$item->addChild('pubDate', "$row->timestamp");	
		}
		header("Content-type:text/xml; charset=utf-8");
		echo $items->asXML();
	}
	
	// purge sabnzbd history
	if($action == "purge_sabnzbd_history"){
		getPageData("https://home.helror.se:9090/api?mode=history&name=delete&value=all&apikey=$sabnzbd_api_key");
	}
	
	//returns XML data for all torrents in Transmission
	if($action == "get_tm_data"){
		$xmlstr = '<?xml version="1.0" encoding="UTF-8"?>';
		$xmlstr .= "<Response>";
		$xmlstr .= "<title>Transmission Data</title>";
		$xmlstr .= "<Items>";
		$xmlstr .= "</Items>";
		$xmlstr .=	"</Response>";
		
		$items = new SimpleXMLElement($xmlstr);
		
		$lines = shell_exec("transmission-remote $transmission_port -n $transmission_user:$transmission_pass -l");
		$alines = split("\n",$lines);
		foreach($alines as $line) {
			$leech = 0;
			$line = trim($line);
			$id = trim(substr($line,0,4));
			$id = str_replace("*","",$id);
			if(is_numeric($id)) {
				$title = trim(substr($line,67));
				$ratio = trim(substr($line,48,6));
				$data = shell_exec("transmission-remote $transmission_port -n $transmission_user:$transmission_pass -t $id -i");
				$tdata = shell_exec("transmission-remote $transmission_port -n $transmission_user:$transmission_pass -t $id -it");
				$tlines = split("\n",$tdata);
				foreach($tlines as $tline){
					$tline = trim($tline);
					if(preg_match("/http/",$tline)) {
						preg_match('/\/\/(.*?):/s', $tline, $trackers);
						$tracker = $trackers[1];
					}
				}
				$blines = split("\n",$data);
				foreach($blines as $bline) {
					$bline = trim($bline);
					if(preg_match("/Percent Done:/",$bline)) {
						$percent_done = str_replace("%","",trim(substr("$bline",14)));
					}
					if(preg_match("/State:/",$bline)) {
						$state = trim(substr("$bline",7));
					}
					if(preg_match("/Total size:/",$bline)) {
						$total_size = fixValue(substr("$bline",12));
					}
					if(preg_match("/Downloaded:/",$bline)) {
						$downloaded = fixValue(substr("$bline",12));
					}
					if(preg_match("/Uploaded:/",$bline)) {
						$uploaded = fixValue(substr("$bline",9));
					}                    
					if(preg_match("/Location:.*leech/",$bline)) {
						$leech = 1;
					}
					if(preg_match("/Location:/",$bline)) {
						$bline = rtrim($bline, '/');
						$category = split("/",trim(str_replace("Location: ","",$bline)));
						$clength = count($category);
						$category = $category[$clength - 1];
					}
					if(preg_match("/Date added:/",$bline)) {
						$newdate = trim(substr("$bline",12));
						$newdate = str_replace("  ", " ", $newdate);
						$datesplit = split(" ","$newdate");
						$day = trim($datesplit[2]);
						if(strlen("$day") < 2) {
							$day = "0"."$day";
						}
						$month = trim($datesplit[1]);
						$year = trim($datesplit[4]);
						$time = trim($datesplit[3]);
						$added = date("Y-m-d H:i:s", strtotime("$year-$month-$day $time"));
					}
				}

				$item = $items->Items->addChild('item');
				$item->addChild('id', "$id");
				$item->addChild('added', "$added");
				$item->addChild('state', "$state");
				$item->addChild('title', "$title");
				$item->addChild('category', "$category");
				$item->addChild('tracker', "$tracker");
				$item->addChild('total_size', "$total_size");
				$item->addChild('downloaded', "$downloaded");
				$item->addChild('uploaded', "$uploaded");
				$item->addChild('percent_done', "$percent_done");
				$item->addChild('ratio', "$ratio");
				$item->addChild('leech', "$leech");
			}
		}
		header("Content-type:text/xml; charset=utf-8");
		echo $items->asXML();	
	}   
}
?>