<?php
error_reporting(E_ALL);
include('db_connect.inc');
if(($_SESSION['logged_in'] === '1') && ($_SESSION['admin'] === '1'))
{  
    //rss feeds
    $feeds_query = "SELECT * FROM rss_feeds ORDER BY active DESC, site ASC";
    $feeds_result = mysql_query($feeds_query);
    echo "<table align=\"center\">";
    echo "<thead>";
    echo "<th width=\"150\">Site</th>";
    echo "<th width=\"50\">Active</th>";	
    echo "<th width=\"50\">Type</th>";
    echo "<th width=\"40\">Delay</th>";	
	echo "<th width=\"40\">Leech</th>";
	echo "<th width=\"80\">Category</th>";
    echo "<th>Address</th>";
    echo "<th width=\"70\">UL</th>";	
    echo "<th width=\"120\">Last Fetch</th>";
	echo "<th width=\"40\">Edit</th>";
    echo "</thead>";
    while($object = mysql_fetch_object($feeds_result))
    {
        $ul = round((($object->uploaded - $object->downloaded) / 1024), 2)." GB";
        $id = $object->id;
        echo "<tr class=\"latest\">";
        echo "<td><input type=\"text\" id=\"rss_site$id\" value=\"$object->site\"></td>";
        echo "<td><input type=\"text\" id=\"rss_active$id\" value=\"$object->active\"></td>";        
		echo "<td><input type=\"text\" id=\"rss_type$id\" value=\"$object->type\"></td>";
        echo "<td><input type=\"text\" id=\"rss_delay$id\" value=\"$object->delay\"></td>";		
		echo "<td><input type=\"text\" id=\"rss_leech$id\" value=\"$object->leech\"></td>";
		echo "<td><input type=\"text\" id=\"rss_leech_category$id\" value=\"$object->leech_category\"></td>";
        echo "<td><input type=\"text\" id=\"rss_address$id\" value=\"$object->address\"></td>";
        echo "<td>$ul</td>";		
		echo "<td><input type=\"text\" id=\"rss_last_hit$id\" value=\"$object->last_hit\"></td>";
		echo "<td><img src=\"/images/Delete-icon.png\" width=\"15\" height=\"15\" onclick=\"systemSettings('del_rss','$id')\" onmouseover=\"this.style.cursor='pointer'\"/><img src=\"/images/Save-icon.png\" width=\"15\" height=\"15\" style=\"padding-left:6px;\" onclick=\"systemSettings('upd_rss','$id')\" onmouseover=\"this.style.cursor='pointer'\"/></td>";
        echo "</tr>"; 
    }
    echo "</table>";
    echo "<br>";

    //categories
    $categories_query = "SELECT * FROM categories";
    $categories_result = mysql_query($categories_query);
    echo "<table align=\"center\">";
    echo "<thead>";
    echo "<th width=\"70\">Name</th>";
    echo "<th width=\"240\">In Name</th>";
    echo "<th>Not In Name</th>";
    echo "<th width=\"65\">EP Check</th>";
	echo "<th width=\"40\">Edit</th>";
    echo "</thead>";
    while($object = mysql_fetch_object($categories_result))
    {
        $id = $object->id;
        echo "<tr class=\"latest\">";
        echo "<td><input type=\"text\" id=\"cat_name$id\" value=\"$object->name\"></td>";
        echo "<td><input type=\"text\" id=\"cat_in_name$id\" value=\"$object->in_name\"></td>";
        echo "<td><input type=\"text\" id=\"cat_not_in_name$id\" value=\"$object->not_in_name\"></td>";
        echo "<td><input type=\"text\" id=\"cat_episode_check$id\" value=\"$object->episode_check\"></td>";
		echo "<td><img src=\"/images/Delete-icon.png\" width=\"15\" height=\"15\" onclick=\"systemSettings('del_cat','$id')\" onmouseover=\"this.style.cursor='pointer'\"/><img src=\"/images/Save-icon.png\" width=\"15\" height=\"15\" style=\"padding-left:6px;\" onclick=\"systemSettings('upd_cat','$id')\" onmouseover=\"this.style.cursor='pointer'\"/></td>";
        echo "</tr>"; 
    }
    echo "</table>";
    echo "<br>";
    
    //system settings
    $system_query = "SELECT * FROM system_settings";
    $system_result = mysql_query($system_query);
    echo "<table align=\"center\">";
    echo "<thead>";
    echo "<th width=\"150\">Variable</th>";
    echo "<th>Data</th>";
	echo "<th width=\"40\">Edit</th>";
    echo "</thead>";
    while($object = mysql_fetch_object($system_result))
    {      
        $id = $object->id;
		$variable = stripslashes($object->variable);
		$data = stripslashes($object->data);
        echo "<tr class=\"latest\">";
        echo "<td><input type=\"text\" id=\"variable$id\" value=\"$variable\"></td>";
		if(preg_match("/pass/i",$object->variable)){
			echo "<td><input type=\"password\" id=\"data$id\" value=\"$data\"></td>";
		} else {
			echo "<td><input type=\"text\" id=\"data$id\" value=\"$object->data\"></td>";
		}
		echo "<td><img src=\"/images/Delete-icon.png\" width=\"15\" height=\"15\" onclick=\"systemSettings('del_var','$id')\" onmouseover=\"this.style.cursor='pointer'\"/><img src=\"/images/Save-icon.png\" width=\"15\" height=\"15\" style=\"padding-left:6px;\" onclick=\"systemSettings('upd_var','$id')\" onmouseover=\"this.style.cursor='pointer'\"/></td>";
        echo "</tr>"; 
    }
    echo "</table>";
    echo "<br>";
    
    function file_size($size)
    {
        $filesizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
        return $size ? round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i] : '0 Bytes';
    }
}
?>