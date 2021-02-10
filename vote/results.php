<?php
session_start();
require_once("shared.php");
$configs = include('config.php');

$mysql_host = $configs["mysql_host"];
$mysql_username = $configs["mysql_username"];
$mysql_password = $configs["mysql_password"];
$mysql_database = $configs["mysql_database"];

$mysqli = new mysqli($mysql_host, $mysql_username, $mysql_password, $mysql_database);
if ($mysqli->connect_error) {
		die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
	}

if($_GET){
  if(is_numeric($_GET["id"])){
    $stmt = $mysqli->prepare("SELECT * FROM `votation` WHERE `id` = ? ");
    $stmt->bind_param("i",$_GET["id"]);

    if($stmt->execute()){
      $result = $stmt->get_result();
      if($result->num_rows == 1){
        $row = $result->fetch_assoc();
        $structure = json_decode($row["structure"],true);
        $vresults = new results($structure);
        $stmt2 = $mysqli->prepare("SELECT * FROM `vt_".$_GET["id"]."`");
        //$stmt->bind_param("i",$_GET["id"]);
        echo "SELECT * FROM `vt_".$_GET["id"]."`";
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        //print_r($result2);
        while($row2 = $result2->fetch_assoc()) {
          $vresults->insertrow($row2);
        }
        $vresults->printresults();
        echo $vresults->GetResultsHTML();

      }

    }
  }
}

sleep(1);
$time = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
echo "Process Time: {$time}";
?>
