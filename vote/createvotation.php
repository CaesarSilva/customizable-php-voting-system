<?php
$configs = include('config.php');
//mysql credentials
$mysql_host = $configs["mysql_host"];
$mysql_username = $configs["mysql_username"];
$mysql_password = $configs["mysql_password"];
$mysql_database = $configs["mysql_database"];

$mysqli = new mysqli($mysql_host, $mysql_username, $mysql_password, $mysql_database);


$vk = "3433"; //verification key - you can only create a new poll with this key
class dbcol{
  public $name ;
  public $type ;
  public $size ;
  public function Getpsql(){//get partial sql
    //
    if($this->type == "VARCHAR"){
      return "`".$this->name."` VARCHAR(".$this->size.")";
    }
  }
  function __construct($name ,$type , $size) {
    $this->name = $name;
    $this->type = $type;
    $this->size = $size;
  }
}
$ColArr = [];// array in which the columns(dbcol) are going to be stored;
if($_POST){
  if($_POST["vkey"] == $vk){
    $sArr = json_decode($_POST["sjson"],true);
    if($sArr != null){
      $questioncount = 0;
      foreach($sArr as $element){
        if(isset($element["type"])){
          if($element["type"] == "rate-categories"){
            for($i = 0 ; $i < count($element["lines"]) ; $i++){
              //
              for($ii = 0 ; $ii <count($element["categories"]) ; $ii++){
                //
                $cname = "q".$questioncount."l".$i."c".$ii;
                $ColArr[] = new dbcol($cname,"VARCHAR", 50);

              }
            }
          }//if($element["type"] == "rate-categories")


        }//if(isset($element["type"]))
        $questioncount ++;
      }//foreach($sArr as $element)
      $stmt = $mysqli->prepare("INSERT INTO `votation` (`name`, `structure`) VALUES (?, ?); ");
    	$stmt->bind_param('ss', $_POST["name"], $_POST["sjson"]); // 's' specifies the variable type => 'string'

    	if(!$stmt->execute()){
          echo "Error insert into:".$stmt->error;
      }
      $vID = $mysqli->insert_id;



      $tsql = "CREATE TABLE `$mysql_database`.`vt_$vID` ".
      "( `id` INT NOT NULL AUTO_INCREMENT , `voterID` VARCHAR(20) NOT NULL , `timestamp` INT NOT NULL ";
      foreach($ColArr as $col){
        $tsql .= ", ".$col->Getpsql();
      }
      $tsql .= " , PRIMARY KEY (`id`)) ENGINE = InnoDB;";
      echo "SQL <BR> $tsql <BR>";
      $stmt2 = $mysqli->prepare($tsql);
      //$stmt2->execute();
      if(!$stmt2->execute()){
          echo "Error insert into:".$stmt2->error;
      }
    }else{
      echo "invalid json";
    }
  }
}
 ?>
<html>
<head>
<title>Crete new votation</title>
</head>
<body>
<form method="post">
<table border="1">
<tr>
<td>create votation key</td>
<td><input type="text" name="vkey"/>
</td>
</tr>

<tr>
<td>name</td>
<td><input type="text" name="name"/>
</td>
</tr>

<tr>
<td>structure json</td>
<td><textarea name="sjson" rows="4" cols="50"></textarea>
</td>
</tr>

</table>
<input type="submit"/>
</form>
</body>
</html>
