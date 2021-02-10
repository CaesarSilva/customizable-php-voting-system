<?php
session_start();
require_once("shared.php");
$configs = include('config.php');
$mysql_host = $configs["mysql_host"];
$mysql_username = $configs["mysql_username"];
$mysql_password = $configs["mysql_password"];
$mysql_database = $configs["mysql_database"];
$mysqli = new mysqli($mysql_host, $mysql_username, $mysql_password, $mysql_database);
$sessionOK = false;//true when a session is valid
if($_POST){
  $idsplit = explode("@",$_POST["id"]);
  print_r($idsplit);
  if($idsplit[1] == "0000"){
    $_SESSION["SESSION_ID"] = $idsplit[0]."@-1";//test session
    $_SESSION["SESSION_KEY"]= "0000";
    echo "=0000";

  }else{
    $stmt = $mysqli->prepare("SELECT * FROM `voters` WHERE `voterID` = ? AND `loginKEY` = ?");
    $stmt->bind_param('ss', $_POST["id"], $_POST["key"]);
    if($stmt->execute()){
      $result = $stmt->get_result();
      if($result->num_rows == 1){
        $_SESSION["SESSION_ID"] = $_POST["id"];//that's not the session id, just the voter id
        $_SESSION["SESSION_KEY"]= random_str(12);//this is useless unless it's stored in a database
      }elseif($result->num_rows == 0){
        echo "Incorrect id and/or key";
      }
    }else{
      echo "Error ".$stmt->error;
    }

  }
  }
  if($_SESSION){
    echo "session detected";
    $sessionidsplit = explode("@", $_SESSION["SESSION_ID"]);
    print_r($sessionidsplit);
    if($sessionidsplit[1] == "-1"){//test session
      echo "$sessionOK = true;";
      $sessionOK = true;

    }else{//there's no session verification
      $sessionOK = true;
    }
  }

?>

<html>
<head>
  <title> Voting system </title>
</head>
<body>
  <?php
  //show form if there is not a valid session active
  if(!$sessionOK){
    echo "<form method=\"post\">
    <table>
    <tr>
    <td>id:</td>
    <td>key:</td>
    </tr>
    <tr>
    <td><input type=\"text\" name=\"id\"></td>
    <td><input type=\"text\" name=\"key\"></td>
    </tr>
    <tr>
    <td><input type=\"submit\"></td>
    </tr>
    </table>

    </form>";

  }else{
    echo "
    Welcome
    </BR>
    <a href=\"votation.php\">Click here to vote</a>
    <BR><a href=\"logout.php\">Click here to log out</a>
    ";

  }
  ?>
</body>
</html>
