<?php
include( "config.php" );
$link = mysql_connect( DB_HOST, DB_USER, DB_PASS );
mysql_select_db( DB_NAME, $link );

$query = "SELECT username,COUNT(username) as w FROM (SELECT username,objectID FROM `checks` GROUP BY username,objectID) as t GROUP BY username ORDER BY w DESC Limit 10";
$result = mysql_query( $query ) or die( mysql_error() );

$string = "<html><head><meta charset=\"utf-8\"></head><body><a href='map.png'><img src='map_small.png'/></a><br/><table border=1><tr><th></th><th>Top 10 Username</th><th>Checks</th></tr>";
$i = 0;
while($row = mysql_fetch_array( $result )){
	$i++;
	$string .= "<td>" . $i . "</td><td>" . $row[0] . "</td><td>" . $row[1] . "</td></tr>";
}
$string .= "</table>";

$string .= "<table border='1'>";
//Unique users
$query = "SELECT COUNT(*) as w FROM (SELECT username FROM `checks` GROUP BY username) as t";
$result = mysql_query( $query ) or die( mysql_error() );
$row = mysql_fetch_array( $result );
$string .= "<tr><td>Unique users</td><td>" . $row[0] . "</td></tr>";
//Checks
$query = "SELECT COUNT(*) FROM `checks`";
$result = mysql_query( $query ) or die( mysql_error() );
$row = mysql_fetch_array( $result );
$string .= "<tr><td>Checks</td><td>" . $row[0] . "</td></tr>";
$string .= "</table>";

//Gen img LARGE
$query = "SELECT * FROM (SELECT COUNT(*) as c,lon,lat FROM (SELECT 1,ROUND(lon, 1) as lon,ROUND(lat, 1) as lat FROM checks) as t GROUP BY lon,lat) as w ORDER BY c DESC";
$result = mysql_query( $query ) or die( mysql_error() );
$gd = imagecreatetruecolor( 3600, 1800 );
$i = 0;
while($row = mysql_fetch_array( $result )){
	if($i == 0)
		$max = $row[0];
	$i++;
	$color = imagecolorallocate($gd, round( 255 * ( $row[0] / $max )), 255, round( 255 * ( $row[0] / $max )));
	imagesetpixel($gd, 1800 + round( $row[1] * 10 ), 900 - round( $row[2] * 10 ), $color );
}
imagepng( $gd, "stats/map.png" );

//Gen img SMALL
$query="SELECT * FROM (SELECT COUNT(*) as c,lon,lat FROM (SELECT 1,ROUND(lon) as lon,ROUND(lat) as lat FROM checks) as t GROUP BY lon,lat) as w ORDER BY c DESC";
$result = mysql_query( $query ) or die( mysql_error() );
$gd = imagecreatetruecolor( 360, 180 );
$i=0;
while($row = mysql_fetch_array( $result )){
	if($i == 0)
		$max = $row[0];
	$i++;
	$color = imagecolorallocate($gd, round( 255 * ( $row[0] / $max )), 255, round( 255 * ( $row[0] / $max )));
	imagesetpixel($gd, 180 + round( $row[1] ), 90 - round( $row[2] ), $color );
}
imagepng( $gd, "stats/map_small.png" );

$string .= "</body></html>";
$file = fopen ( "stats/index.html", 'w' );
fwrite ( $file, $string );
fclose ( $file );
?>