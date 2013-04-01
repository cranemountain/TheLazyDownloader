<?php
error_reporting(E_ALL);
include('rss_db_connect.php');
include('config.inc');
$torrent_verify_check_time = date('m');

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

function get_data($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}

/*
//insert finished nzbs to completed_downloads
$sabnzbd_xml = simplexml_load_string(get_data("https://localhost:9090/api?mode=history&output=xml&apikey=$sabnzbd_api_key"));
foreach($sabnzbd_xml->slots->slot as $sabnzbdinfo) {
	$title = $sabnzbdinfo->name;
	$type = $sabnzbdinfo->category;
	$status = $sabnzbdinfo->status;
	if($status == "Completed"){
		mysqli_query($mysqli, "INSERT INTO completed_downloads (title,type,download_type) VALUES ('$title','$type','nzb')");
	}	
}
*/
//purge torrents
mysqli_query($mysqli, "DELETE FROM transmission");
$transmission_xml = simplexml_load_string(get_data("http://localhost/torrents/api.php?action=get_tm_data&key=$system_api_key"));
foreach($transmission_xml->Items->item as $transmissioninfo) {
    $id = $transmissioninfo->id;
    $title = $transmissioninfo->title;
    $type = $transmissioninfo->category;
    $percent_done = $transmissioninfo->percent_done;
    $added = $transmissioninfo->added;
    $total_size = $transmissioninfo->total_size;
    $downloaded = $transmissioninfo->downloaded;
    $uploaded = $transmissioninfo->uploaded;
    $ratio = $transmissioninfo->ratio;
    $leech = $transmissioninfo->leech;
    $tracker = $transmissioninfo->tracker;
    $site = explode(".",$tracker);
    $site = $site[(count($site) - 2)];
    $state = $transmissioninfo->state;
    $days = round((strtotime(date("Y-m-d H:i:s")) - strtotime($added)) / (60 * 60 * 24),3);
    $delete = "false";

    /*
    if($percent_done >= 100) {
        if($uploaded >= ($total_size * 10)){
            $delete = "true";
        }
        if($uploaded >= ($total_size * 5.0) && $days >= ($max_torrent_age - 2.5)){
            $delete = "true";
        }
        if($uploaded >= ($total_size * 2.5) && $days >= ($max_torrent_age - 1.5)){
            $delete = "true";
        }
        if($uploaded >= ($total_size * 1.5) && $days >= ($max_torrent_age - 0.5)){
            $delete = "true";
        }
        if($uploaded >= 500 && $downloaded < 100 && $days >= 0.5) {
            $delete = "true";
        }
    }
    */
    if($days >= $max_torrent_age) {
        $delete = "true";
    }
    if($delete == "true") {
        exec("transmission-remote $transmission_port -n $transmission_user:$transmission_pass -t $id -r");
        if($leech == 0){
            mysqli_query($mysqli, "UPDATE rss_feeds SET uploaded = uploaded + $uploaded, downloaded = downloaded + $downloaded WHERE site LIKE '%$site%'");
        }elseif($leech == 1){
            mysqli_query($mysqli, "UPDATE rss_feeds SET uploaded = uploaded + $uploaded WHERE site LIKE '%$site%'"); 
        }
    }else{
		echo $id."-".$title."\n";
        mysqli_query($mysqli, "INSERT INTO transmission (id,added,title,tracker,percent_done,ratio,age,total_size,downloaded,uploaded,leech) VALUES ('$id','$added','$title','$tracker','$percent_done','$ratio','$days','$total_size','$downloaded','$uploaded','$leech')");
    }
    if($percent_done == "100"){
		$title = str_replace(" ",".",$title);
		mysqli_query($mysqli, "INSERT INTO completed_downloads (title,type,download_type,leech) VALUES ('$title','$type','torrent','$leech')");
	}
	if($state == "Stopped"){
        shell_exec("transmission-remote $transmission_port -n $transmission_user:$transmission_pass -t $id -s");
    }
	if(($days > 0.05 && $percent_done == "0") && $torrent_verify_check_time == "20"){
        shell_exec("transmission-remote $transmission_port -n $transmission_user:$transmission_pass -t $id --reannounce");
		shell_exec("transmission-remote $transmission_port -n $transmission_user:$transmission_pass -t $id -v");
    }
}

//add extra trackers to transmission
$max_age = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')." - 24 hours"));
$atquery = "SELECT * FROM completed_downloads WHERE timestamp > '$max_age' AND leech = '0' ORDER BY id DESC";
if ($atresult = $mysqli->query($atquery)) {
	while ($atobj = $atresult->fetch_object()) {
		$torg = array(" ","_");
		$title = str_ireplace($torg, ".", $atobj->title);
		$type = $atobj->type;	
		$toquery = "SELECT * FROM torrents WHERE title REGEXP '^$title' AND type = 'torrent'";
		if ($toresult = $mysqli->query($toquery)) {
			while ($toobj = $toresult->fetch_object()) {		
				$site = $toobj->site;
				$link = $toobj->link;
				$link_type = $toobj->type;
				if($type != "leech" || $type != ""){
					mysqli_query($mysqli, "INSERT INTO active_downloads (site,title,type,link,download_type) VALUES ('$site','$title','$type','$link','$link_type')");
				}
			}
		}
	}
	// free result set
	$atresult->close();
	$toresult->close();
}

$query = "SELECT * FROM active_downloads WHERE started = '0'";
if($result = $mysqli->query($query)){
    while($obj = $result->fetch_object()){
        $id = $obj->id;
        $link = $obj->link;
        $type = $obj->type;
        exec("transmission-remote $transmission_port -n $transmission_user:$transmission_pass -a '$link' -v -w '$complete_path/$type/'");
        mysqli_query($mysqli, "UPDATE active_downloads SET started = '1' WHERE id = '$id'");
        
        // delete old
        $old = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s'). ' - 7 days'));
        mysqli_query($mysqli, "DELETE FROM active_downloads WHERE timestamp < '$old'");
    }
}
?>
