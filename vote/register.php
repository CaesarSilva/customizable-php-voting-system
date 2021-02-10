<?php
session_start();
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
}//TODO put this function and other shared functions in the same php file
$date = new DateTime();
$timestamp = $date->getTimestamp();
$configs = include('config.php');
if($_GET){
  $mysql_host = $configs["mysql_host"];
  $mysql_username = $configs["mysql_username"];
  $mysql_password = $configs["mysql_password"];
  $mysql_database = $configs["mysql_database"];
  $mysqli = new mysqli($mysql_host, $mysql_username, $mysql_password, $mysql_database);
  //http://localhost/vote/register.php?invid=5&invkey=ZZmRJd0YOzKh49V
  if(isset($_GET["invid"], $_GET["invkey"])){
    $stmt = $mysqli->prepare("SELECT * FROM `invitations` WHERE `id` = ?" );
    $stmt->bind_param("i", $_GET["invid"]);
    $stmt->execute();

  	$result = $stmt->get_result();
    if($result->num_rows == 0){
      echo "invitation ID doesn't exist";
    }elseif($result->num_rows == 1){
      //$result = $stmt->get_result();
      $row = $result->fetch_assoc();
      print_r($row);
      if($_GET["invkey"] == $row["invkey"]){
        echo "Invitation is valid";
        //bellow check if invitation was arleady used
        //SELECT * FROM `voters` WHERE `invitationID` = '1'
        $stmt2 = $mysqli->prepare("SELECT * FROM `voters` WHERE `invitationID` = ?" );
        $stmt2->bind_param("i", $_GET["invid"]);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        if($row["uselimit"] == -1 || $row["uselimit"] > $result2->num_rows){
          //Invitation can be used
          //INSERT INTO `voters` (`rID`, `voterID`, `voterdata`, `timestamp`, `invitationID`)
          // VALUES (NULL, 'voterid', 'voterdata from invitation', '11', '22')
          $stmt3 = $mysqli->prepare("INSERT INTO `voters` (`voterdata`, `timestamp`, `invitationID`)"
          ." VALUES (?, ?, ?)");
          $stmt3->bind_param("sii", $row["userdata"],$timestamp,$_GET["invid"]);
          if($stmt3->execute()){
            $insertID = $mysqli->insert_id;
            //UPDATE `invitations` SET `uniqueId` = 'ds5209706280362967066' WHERE `invitations`.`id` = 5
            $stmt4 = $mysqli->prepare("UPDATE `voters` SET `voterID` = ? , `loginKEY` = ? where `voters`.`rID` = ?");
            $voterid = $row["votationID"]."@".$insertID;
            $loginkey = random_str(12);
            $stmt4->bind_param("ssi", $voterid,$loginkey,$insertID);
            if($stmt4->execute()){
              echo "account created";
              echo "<BR /> voterID:$voterid key:$loginkey";
            }
          }else{
            echo "stmt3 error:".$stmt3->error;
          }


        }else{
          echo "This invitation cannot be used anymore";
        }





      }else{
        echo "wrong key";
      }

    }

  }else{
    echo "some parameters are missing";
  }



}


?>
<html>
<head>
  <title>Voter registration</title>
</head>
<body>

</body>
</html>
