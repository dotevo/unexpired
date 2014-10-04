<?php
session_start();
include( "config.php" );

if(!isset( $_SESSION['osm_user'] ))
	die( "AUTHERR" );

if(!isset($_GET['osmid'])||!isset($_GET['type']))
	die( "ERR" );

$link = mysql_connect(DB_HOST,DB_USER,DB_PASS);
mysql_select_db(DB_NAME, $link);

$osmid=intval($_GET['osmid']);
$osmtype=mysql_real_escape_string($_GET['type']);

//Get POI from overpass
$query="[out:json];";
if($osmtype == "way"){
	$query .= "way(" . $osmid . ");out meta center qt;";
}else{
	$query .= "node(" . $osmid . ");out meta qt;";
}

$url = "http://overpass-api.de/api/interpreter?data=" . urlencode( $query );
$con = json_decode( file_get_contents($url), true );

//Version will be needed in future
$version = intval( $con["elements"][0]['version'] );
//lat lon for stats (map)
$lat = floatval( $con["elements"][0]['lat']);
$lon = floatval( $con["elements"][0]['lon']);
$user = mysql_real_escape_string($_SESSION['osm_user']);

$query = "INSERT IGNORE INTO objects (osmid,osmtype) VALUES (".$osmid.",'".$osmtype."');";
$result = mysql_query($query) or die(mysql_error());
$query = "INSERT INTO checks (objectID,version,lat,lon,username) VALUES ((SELECT id FROM objects WHERE osmtype='".$osmtype
	."' AND osmid=".$osmid." LIMIT 1),".$version.",".$lat.",".$lon.",'".$user."');";
$result = mysql_query($query) or die(mysql_error());
echo "OK";
?>