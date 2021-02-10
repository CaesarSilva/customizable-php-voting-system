<?php
session_start();
require_once("shared.php");
$configs = include('config.php');
$allowtestvote = true; //allows voting using a test account
$requiredfields = [];
$alreadyvoted = false;
$testaccount = false;
/*class dbcol{
  public $name ;
  public $type ;
  public $size ;
  public function Getpsql($onlyname = false){//get partial sql
    //
    if($onlyname){
      return "`".$this->name."`";
    }
    if($this->type == "VARCHAR"){
      return "`".$this->name."` VARCHAR(".$this->size.")";
    }
  }
	public function GetPType(){
		if($this->type == "VARCHAR"){
			return "s";
		}else if($this->type == "INT"){
			return "i";
		}
	}
  function __construct($name ,$type , $size) {
    $this->name = $name;
    $this->type = $type;
    $this->size = $size;
  }
}*/

$votationid = -1 ;// -1 = no votation selected

//mysql credentials
$mysql_host = $configs["mysql_host"];
$mysql_username = $configs["mysql_username"];
$mysql_password = $configs["mysql_password"];
$mysql_database = $configs["mysql_database"];

$mysqli = new mysqli($mysql_host, $mysql_username, $mysql_password, $mysql_database);
if ($mysqli->connect_error) {
		die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
	}
	if($_SESSION){
		//echo "session detected";
		$sessionidsplit = explode("@", $_SESSION["SESSION_ID"]);
		//print_r($sessionidsplit);
		//echo "$sessionOK = true;";
		$votationid = (int) $sessionidsplit[0];
    $voterID = $_SESSION["SESSION_ID"];
    $testaccount = $sessionidsplit[1] == "-1";
    //echo "<BR > \$testaccount:$testaccount type:".gettype($testaccount)." ".$sessionidsplit[1]."<BR \>";
  $smt6 = $mysqli->prepare("SELECT * FROM `vt_$votationid` where `voterID` = ?");
  $smt6->bind_param("s",$voterID);
  $smt6->execute();
  $result6 = $smt6->get_result();
  //echo "SELECT * FROM `vt_$votationid` where `voterID` = '$voterID'";
  if($result6->num_rows != 0){
    $alreadyvoted = true;
    echo "You have already voted.";
  }
if($votationid > -1){

  $stmt = $mysqli->prepare("SELECT * FROM `votation` WHERE `id` = ?");
	$stmt->bind_param('i', $votationid); // 's' specifies the variable type => 'string'

	$stmt->execute();

	$result = $stmt->get_result();
	//print_r($result);
  $row = $result->fetch_assoc();
	//echo "</BR>test";
	//print_r($row);
	$structure = json_decode($row["structure"],true);
  //echo $row["structure"];
	//echo "count".count($structure);
}
$ColArr = [];
$ValArr = [];
if(is_array($structure) && $_POST){
$allowvote = true;
$questioncount = 0;
//echo "BEFOREFOREACH";
foreach($structure as $question){
	//echo "fe1";
	if($question["type"] == "rate-categories"){
	//	echo "fe1";
		for($i = 0; $i < count($question["lines"]);$i++){
			for($ii =0; $ii <count($question["categories"]); $ii++){
				$cname = "q".$questioncount."l".$i."c".$ii;
				//echo "<BR/>$cname :".$_POST[$cname]."<BR/>";
				if(isset($_POST[$cname])){
					//echo "<BR/>$cname :".$_POST[$cname]."<BR/>";
					$ColArr[] = new dbcol($cname,"VARCHAR", 50);
				}else {
				//	echo "<BR/>$cname : empty<BR/>";
					$allowvote = false;
				}

			}
		}

	}
	$questioncount ++;
}
echo "<BR />alreadyvoted: $alreadyvoted ; allowvotetestacc".(int)$configs["allowvotetestacc"]. "testacc".(int)$testaccount." <BR />";
if($allowvote && (!$alreadyvoted || ($configs["allowvotetestacc"] && $testaccount)))
{
  echo "line 111";
	$vsql = "INSERT INTO `vt_$votationid`"." (`voterID`, `timestamp` ";
	$pString = "si"; // voterID = string ; timestamp = int
	$valueQM = "?, ?";//question marks used by bind_param
  //$voterID = "hcodedid";
  $date = new DateTime();
  $timestamp = $date->getTimestamp();
  $values = array(&$voterID, &$timestamp);
	foreach($ColArr as $col){
		$vsql .= ", ".$col->Getpsql(true);
		$pString .= $col->GetPType();
		$valueQM .= ", ?";
    $values[] = &$_POST[$col->name];
  }
  $vsql .=") VALUES (".$valueQM.")";
	//echo $vsql."<BR>" ;
  //print_r($values);
	$stmt = $mysqli->prepare($vsql);
  $arrayparameters = array(&$pString);
  $arrayparameters = array_merge($arrayparameters,$values);
  $arrayparameters2 = array_merge(array(&$stmt), $arrayparameters);
  //echo "<br>";
  //print_r($arrayparameters2);
  //call_user_func_array(array($stmt,"bind_param"), $arrayparameters);
  call_user_func_array("mysqli_stmt_bind_param",$arrayparameters2);
  //$stmt->bind_param(, $_POST["name"], $_POST["sjson"]); // 's' specifies the variable type => 'string'

	$stmt->execute();

}else{
  echo "Vote not registred";
}

}

}
function parse_rate_categories($ArrIn , $qID, &$reqf=[]){
	//echo "</BR>BELLOW printr</BR>";
	//print_r($ArrIn);
	$returnvar = "<table border=\"1\"> <tr><td></td>";
	foreach($ArrIn["categories"] as $category){
		$returnvar .= "<td>".$category."</td>";
	}
	$returnvar .= "</tr>\n";
	$linecount = 0;
	foreach($ArrIn["lines"] as $line){
		$returnvar .="<tr>\n <td>".$line."</td>";
		for($i = 0 ; $i < count($ArrIn["categories"]); $i++){
			$returnvar .="<td id=\"td_q".$qID."l".$linecount."c".$i."\">";
			foreach($ArrIn["ratings"] as $rating){
				$returnvar .= $rating."<input type=\"radio\" name=\"q".$qID."l".$linecount."c".$i."\" value=\"".$rating."\"/>";
				//echo "//".count($ArrIn["ratings"]);
			}
			$reqf[] = "q".$qID."l".$linecount."c".$i;
			$returnvar .= "</td>";
		}

		$returnvar .= "</n>\n";
		$linecount ++;
	}

	$returnvar .= "\n</table>";
	return $returnvar;

}

?>
<html>
<head>
<title> Votation page </title>
<script>
function onc(elements){
	let allowvote = true;
	elements.forEach((el)=>{
		let radios = document.getElementsByName(el);
		let checked = false;
		for (let i = 0, length = radios.length; i < length; i++) {
  	if (radios[i].checked) {
		checked = true;
		break;
  	}
}
		if(!checked){
			allowvote = false;
			radios[0].parentElement.style.backgroundColor = "red";
			for (let i = 0, length = radios.length; i < length; i++) {
	  	radios[i].onclick = function(){
				radios[0].parentElement.style.backgroundColor = "green";
			}
	}
		}
	});
	if(allowvote){
		document.getElementById("form").submit();
	}else{
		alert("Please fill all the required fields");
	}
}
</script>
</head>
<body>
<?php
if(is_array($structure)){
	//echo "SUCCESS";
	echo "<form id=\"form\" method=\"post\">";
	$elementcount = 0;
	foreach ($structure as &$element) {
    if($element["type"] = "rate-categories"){
			echo parse_rate_categories($element, $elementcount, $requiredfields);

		}
		$elementcount ++;
	}

	$jsarray = "";
	$first = true;
	foreach($requiredfields as $field){
		if(!$first){
			$jsarray .= ", ";
		}
		$jsarray .= "'$field'";
		$first = false;
	}
	echo "<BR/> <input type=\"button\" onClick=\"onc(reqf)\" value=\"Vote!\"/>";
	//echo "<BR/> <input type=\"submit\"/>";
	//uncomment line above to test if the PHP script can handle invalid values
	echo "</form>";
	echo "<script> var reqf = [$jsarray]</script>";
  echo "<BR /> Required fields: <BR />";
  print_r($requiredfields);

}

?>


</body>
</html>
