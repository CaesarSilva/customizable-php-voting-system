<?php
$configs = include('config.php');
$mysql_host = $configs["mysql_host"];
$mysql_username = $configs["mysql_username"];
$mysql_password = $configs["mysql_password"];
$mysql_database = $configs["mysql_database"];
$mysqli = new mysqli($mysql_host, $mysql_username, $mysql_password, $mysql_database);


header('Content-Type: application/json');
$key = $configs["invitationapiKEY"];
$date = new DateTime();
$timestamp = $date->getTimestamp();
$keyL = strlen($key);
if(($keyL % 2) == 1){
  $keyA = substr($key,0,($keyL-1)/2);
  $keyB = substr($key,($keyL-1)/2 , $keyL);
}else{
  $keyA = substr($key,0,$keyL/2);
  $keyB = substr($key,$keyL/2 , $keyL);
}
$output = [];
//$output[] = $keyA;
//$output[] = $keyB;
//print_r($_POST);


function random_str(
    $length,
    $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
) {
    $str = '';
    $max = mb_strlen($keyspace, '8bit') - 1;
    if ($max < 1) {
        throw new Exception('$keyspace must be at least two characters long');
    }
    for ($i = 0; $i < $length; ++$i) {
        $str .= $keyspace[random_int(0, $max)];
    }
    return $str;
}
if($_POST){
  $json = $_POST["json"];
  $hmac = hash_hmac('sha256', $json, $keyA);
  if($hmac == $_POST["hmac"]){
    $reqAr = json_decode($_POST["json"],true);
    if($reqAr["type"] == "on!vote"){
      //echo "onvote match";

      $stmt = $mysqli->prepare("SELECT * FROM `invitations` WHERE `uniqueId` = ? AND `votationID` = ?");
      $stmt->bind_param('si', $reqAr["id"], $reqAr["votation"]);
      $stmt->execute();

      $result = $stmt->get_result();
      //print_r($result);
      //$row = $result->fetch_assoc();
      if($result->num_rows == 0){
        $stmt = $mysqli->prepare("INSERT INTO `invitations` (`uniqueId`, `userdata`, `timestamp`, `invkey`, `votationID`) VALUES (?, ?, ?, ?, ?)" );
        $invkey = random_str(15);
        $userdata = json_encode($reqAr["userData"]);
        $stmt->bind_param('ssisi', $reqAr["id"],$userdata, $timestamp , $invkey, $reqAr["votation"]);
        $stmt->execute();
        $output["rcode"] = 0;
        $output["invkey"] = $invkey;
        $output["invID"] = $mysqli->insert_id;

      }




    }
  }else{
    echo $hmac."!=".$_POST["hmac"];
  }


}else{
  $output["rcode"] = -1;
}





echo json_encode($output);
//-1 no POST REQUEST
//0 success - invitation generated
//1 invitation already exists, no voter was found, invitation key reset
//2 invitation already exist, voter found, voter key reset
?>
