function firstLoad(){
	$("#showing").html('all');
	$("#main").load('/torrents/data_fetch.php?action=getLatestDownloads&days=1&cat=all');
	showSubMenu('downloads');
	$("#systemStats").load('/torrents/data_fetch.php?action=getSystemStats');
}

function showSubMenu(menu){
	if(menu == "downloads"){
		var days = $('#show_days').text();
		if(days == ""){ days = "1"; }
		$("#sub_menu").html(
		"<button style=\"width: 80px;\" onclick='showCat(\"all\")'>All</button>"+
		"<select class=\"menu\" id=\"sel_days\" onchange='setDays()'>"+
		"<option selected value='"+days+"'>"+days+"</option>"+
		"<option value='1'>1</option>"+
		"<option value='2'>2</option>"+
		"<option value='4'>4</option>"+
		"<option value='7'>7</option>"+
		"<option value='14'>14</option>"+
		"<option value='21'>21</option>"+
		"<option value='30'>30</option>"+
		"<option value='90'>90</option>"+
		"</select><br>"+
		"<button style=\"width: 80px;\" onclick='showCat(\"mp3\")'>MP3</button><button onclick=\"editFav('mp3')\">Edit</button><br>"+
		"<button style=\"width: 80px;\" onclick='showCat(\"tv\")'>TV</button><button onclick=\"editFav('tv')\">Edit</button><br>"+
		"<button style=\"width: 80px;\" onclick='showCat(\"movies\")'>Movies</button><button onclick=\"editFav('movies')\">Edit</button><br>"+
		"<button style=\"width: 80px;\" onclick='showCat(\"software\")'>Software</button><button onclick=\"editFav('software')\">Edit</button><br>"+
		"<button style=\"width: 80px;\" onclick='showCat(\"documentry\")'>Documentry</button><button onclick=\"editFav('documentry')\">Edit</button><br>"+
		"<button style=\"width: 80px;\" onclick='showCat(\"audiobooks\")'>Audiobooks</button><button onclick=\"editFav('audiobooks')\">Edit</button><br>"+
		"<button style=\"width: 80px;\" onclick='showCat(\"other\")'>Other</button><button onclick=\"editFav('other')\">Edit</button><br>"+
		"</span>");
	} else if(menu == "transmission") {
		$("#sub_menu").html(
		"<button style=\"width: 115px;\" onclick='transmissionAction(\"\")'>Add Torrent</button><br>"+
		"<button style=\"width: 115px;\" onclick='transmissionAction(\"\")'>DL Speed</button><br>"+
		"<button style=\"width: 115px;\" onclick='transmissionAction(\"\")'>UL Speed</button><br>"+
		"");
	} else if(menu == "sabnzbd") {
		$("#sub_menu").html(
		"<button style=\"width: 115px;\" onclick='sabnzbdAction(\"addNZB\")'>Add NZB</button><br>"+
		"<button style=\"width: 115px;\" onclick='sabnzbdAction(\"purgeHistory\")'>Purge History</button><br>"+
		"");
	} else if(menu == "admin") {
		$("#sub_menu").html("");
	}	
}

function sabnzbdAction(action){
	var apikey = $("#system_api_key").val();
	var url = "/torrents/api.php";
	if(action == "purgeHistory"){
		var cAction = "purge_sabnzbd_history";
		$.get(url, { action: cAction, key: apikey })
		.always(function() { alert("Done"); });
		loadSABnzbd();
	}
}

function transmissionAction(action,id){
	var apikey = $("#system_api_key").val();
	var url = "/torrents/api.php";
	if(action == "delete_torrent"){
		$.get(url, { action: action, key: apikey, id: id })
		.always(function() { alert("Done"); });
		loadTransmission();
	}
}
function systemSettings(action,id){
	var apikey = $("#system_api_key").val();
	var url = "/torrents/api.php";
	if(action == "upd_rss"){
		var active = $('#rss_active'+id).val();
		var site = $('#rss_site'+id).val();
		var leech = $('#rss_leech'+id).val();
		var leech_category = $('#rss_leech_category'+id).val();
		var type = $('#rss_type'+id).val();
		var delay = $('#rss_delay'+id).val();
		var address = $('#rss_address'+id).val();
		$.get(url,  { action: action, active: active, site: site, leech: leech, leech_category: leech_category, type: type, delay: delay, address: address, id: id,key: apikey } )
		.always(function() { alert(site + " updated!"); });
	}
	if(action == "upd_var"){
		var variable = $('#variable'+id).val();
		var data = $('#data'+id).val();
		$.get(url,  { action: action, variable: variable, data: data, id: id, key: apikey } )
		.always(function() { alert(variable + " updated!"); });
	}
	if(action == "upd_cat"){
		var name = $('#cat_name'+id).val();
		var in_name = $('#cat_in_name'+id).val();
		var not_in_name = $('#cat_not_in_name'+id).val();
		var episode_check = $('#cat_episode_check'+id).val();
		$.get(url,  { action: action, name: name, in_name: in_name, not_in_name: not_in_name, episode_check: episode_check, id: id,key: apikey } )
		.always(function() { alert(name + " updated!"); });
	}
}

function favAction(action,id){
	var apikey = $("#system_api_key").val();
	var url = "/torrents/api.php";
	if(action == "del_fav"){
		if(confirm("Really remove favorite?")){
			$.get(url,  { action: action, id: id, key: apikey } )
			.always(function() { alert(id + " removed!"); });
		}
	}
	if(action == "upd_fav"){
		var name = $('#name'+id).val();
		var not_in_name = $('#not_in_name'+id).val();
		$.get(url,  { action: action, name: name, not_in_name: not_in_name, id: id, key: apikey } )
		.always(function() { alert(name + " updated!"); });
	}
	if(action == "add_fav"){
		var apikey = $("#system_api_key").val();
		var url = "/torrents/api.php";	
		var nAction = "add_fav";
		var nName = $("#new_fav_name").val();
		var nNot_in_name = $("#new_fav_not_in_name").val();
		var nCategory = $('#showing').text();
		$.get(url,  { action: nAction, name: nName, not_in_name: nNot_in_name, category: nCategory, key: apikey } )
		.always(function() { alert(nName + " added!"); });
	}
	editFav($("#showing").text());
}

function editFav(str){
	$("#main").load('/torrents/data_fetch.php?action=getFavorites&cat='+str, function() {
		$("#showing").html(str);
		$("#refresh").html('false');
		$("#new_fav_category").text(str);
	});
}

function showCat(str){
	var days = $("#show_days").text();
	$("#main").load('/torrents/data_fetch.php?action=getLatestDownloads&days='+days+'&cat='+str);
	$("#showing").html(str);
	$("#refresh").html('true');
	$("#current_page").html('downloads');
	showSubMenu('downloads');
}

function loadTransmission(){
	$('#main').load('/torrents/data_fetch.php?action=getTransmission');
	showSubMenu("transmission");
	$("#current_page").html('transmission');
	$("#refresh").html('true');
}

function loadSABnzbd(){
	$('#main').load('/torrents/data_fetch.php?action=getSABnzbd');
	showSubMenu("sabnzbd");
	$("#refresh").html('false');
}

function loadAdmin(){
	$('#main').load('/torrents/admin.php');
	showSubMenu("admin");
	$("#refresh").html('false');
}

function setDays(){
	var days = $("#sel_days").val();
	var cat = $("#showing").text();
	$("#show_days").html(days);
	$("#main").load('/torrents/data_fetch.php?action=getLatestDownloads&days='+days+'&cat='+cat);
}

$(document).ready(function() {
	var refreshId = setInterval(function() {
		var type = $('#showing').text();
		var days = $('#show_days').text();
		var page = $("#current_page").text();
		if($('#refresh').text() == "true"){
			if(page == "downloads"){
				$("#main").load('/torrents/data_fetch.php?action=getLatestDownloads&cat='+type+'&days='+days+'&randval='+ Math.random());
			}
			if(page == "transmission"){
				$('#main').load('/torrents/data_fetch.php?action=getTransmission');
			}
		}
		$("#systemStats").load('/torrents/data_fetch.php?action=getSystemStats');
	}, 9000);
	$.ajaxSetup({ cache: false });
});