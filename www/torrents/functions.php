<?php
function decodeSize( $bytes ){
	$types = array( 'B', 'KB', 'MB', 'GB', 'TB' );
	for( $i = 0; $bytes >= 1024 && $i < ( count( $types ) -1 ); $bytes /= 1024, $i++ );
	return( round( $bytes, 2 ) . " " . $types[$i] );
}

function fDate($str,$format){
	$newDate = date($format, strtotime($str));
	return $newDate;
}
?>