<?php
$system_api_key 		= getSettingData('system_api_key');
$sabnzbd_api_key 		= getSettingData('sabnzbd_api_key');
$sabnzbd_nzb_key 		= getSettingData('sabnzbd_nzb_key');
$sabnzbd_autofetch_path = getSettingData('sabnzbd_autofetch_path');
$transmission_host 		= getSettingData('transmission_host');
$transmission_port 		= getSettingData('transmission_port');
$transmission_user 		= getSettingData('transmission_user');
$transmission_pass 		= getSettingData('transmission_pass');
$hours_to_keep_links 	= getSettingData('hours_to_keep_links');
$max_torrent_age 		= getSettingData('max_torrent_age');
$incomplete_path 		= getSettingData('incomplete_path');
$incomplete_path_leech 	= getSettingData('incomplete_path_leech');
$complete_path 			= getSettingData('complete_path');
$complete_path_leech 	= getSettingData('complete_path_leech');

function getSettingData($variable){
   $settingdata = mysql_fetch_object(mysql_query("SELECT * FROM system_settings WHERE variable = '$variable'")); 
   return $settingdata->data;
}
?>