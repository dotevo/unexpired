<?php
include( "config.php" );

function getData($query){
	$link = mysql_connect( DB_HOST, DB_USER, DB_PASS );
	mysql_select_db( DB_NAME, $link );
	
	$url = "http://overpass-api.de/api/interpreter?data=". urlencode($query);
	$con = json_decode(file_get_contents($url),true);

	$el="SELECT checks.*,CONCAT(osmtype,osmid) as objID FROM objects,checks WHERE checks.objectID=objects.id AND objects.osmid IN ";

	//ARRAY
	$idQ = "(";
	for($i = 0;$i < count( $con["elements"] );$i++){
		$idQ .= mysql_real_escape_string( $con["elements"][$i]["id"] );
		if($i < count( $con["elements"] ) - 1)
			$idQ .= ",";
	}
	$idQ .= ")";

	//if empty
	if(count( $con["elements"] ) == 0){
		$json = json_encode( $con );
		echo $json;
		return;
	}

	$result = mysql_query( $el . $idQ ) or die( mysql_error() );

	$a=array();
	while($row = mysql_fetch_array($result)){
		if(!isset($a[$row["objID"]])){
			$a[$row["objID"]]=array();
		}
		$a[$row["objID"]][]=array("username" => $row["username"],"date" => $row["date"],"version" => $row["version"]);
	}

  for($i=0;$i<count($con["elements"]);$i++){
    $con["elements"][$i]["ex"]=$a[$con["elements"][$i]["type"].$con["elements"][$i]["id"]];
  }
  $json = json_encode($con);
  echo $json;

}

  function getQuery($key,$bbox){
    return "way[\"".$key."\"](".$bbox.");out 100 meta center qt;node[\"".$key."\"](".$bbox.");out 100 meta qt;";
  }



if(!isset($_GET['minlat'])||!isset($_GET['minlon'])||!isset($_GET['maxlat'])||!isset($_GET['maxlon']))
  die("I NEEEDdd YOURrr BRAINnnn!");

  $bbox=floatval($_GET['minlat']).",".floatval($_GET['minlon']).",".floatval($_GET['maxlat']).",".floatval($_GET['maxlon']);
  $q=getQuery("shop",$bbox).getQuery("amenity",$bbox);
    getData("[out:json];".$q);

?>
