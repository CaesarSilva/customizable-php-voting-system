<?php
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
class dbcol{
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
}
class votecount{
  public $name = "";
  public $numvotes = 0;
  public $value = 0;
  public $validvalues = [];
  function __construct($vvalues){
    $this->validvalues = $vvalues;
  }
  function insertvote($vote){
    foreach($this->validvalues as $value){
      if($vote == $value){
        if(is_numeric($vote)){
          $this->numvotes++;
          $this->value += (int)$vote;
        }
        break;
      }
    }
  }
}
function sortvc($a,$b){
  //echo "a $a b $b";
  if($a->value > $b->value){
    return -1;
  }elseif($a->value == $b->value){
    return 0;
  }elseif($a->value < $b->value){
    return 1;
  }

}
function ResultHTML($structure,$qr){
  $output = "";
  $js = "";
  $questioncount = 0;
  foreach($structure as $question){
    $output .= "<BR /> <table border=\"1\">";
    //$this ->questiondata[$questioncount]["type"] = $question["type"];
    if($question["type"] == "rate-categories"){
      //$this ->questiondata[$questioncount]["categories"] =
      $output .= "<tr><td />";
      foreach($question["categories"] as $category){
        $output .="<td>$category</td>";
      }
      $output .="</tr>";
      for($i = 0 ; $i < count($question["lines"]) ; $i++){
        $output .= "<tr><td>".$question["lines"][$i]."</td>";
        for($ii = 0 ; $ii <count($question["categories"]) ; $ii++){
          $output .= "<td id=\"td_q".$questioncount."l".$i."c".$ii."\">".$qr[$questioncount][$i][$ii]->value."</td>";
          //$this->questioncol[$questioncount][] = "q".$questioncount."l".$i."c".$ii;
          //$this->questionr[$questioncount][$i][$ii] = new votecount($question["ratings"]);
        }
    }
    for($o = 0 ; $o < count($question["categories"]) ; $o++){
      $rcategory = [];
      for($oo = 0; $oo <count($question["lines"]) ; $oo++){
        $rcategory[] = $qr[$questioncount][$oo][$o];
      }
      usort($rcategory,"sortvc");
      $js.="\ndocument.getElementById('td_".$rcategory[0]->name."').style.backgroundColor=\"Gold\";";
      $js.="\ndocument.getElementById('td_".$rcategory[1]->name."').style.backgroundColor=\"Silver\";";
      $js.="\ndocument.getElementById('td_".$rcategory[2]->name."').style.backgroundColor=\"SaddleBrown\";";

    }
  }
  $questioncount ++;
  $output .= "</table>";
}

  $output .= "<script>$js</script>";
  return $output;
}

class results{
  private $questionr = [];
  //private $questiondata ;
  public $structure = [];
  private $questioncol = [];
  function __construct($structure){
    $this->structure = $structure;
    $questioncount = 0;
    foreach($structure as $question){
      //$this ->questiondata[$questioncount]["type"] = $question["type"];
      if($question["type"] == "rate-categories"){
        //$this ->questiondata[$questioncount]["categories"] =
        for($i = 0 ; $i < count($question["lines"]) ; $i++){
          //
          for($ii = 0 ; $ii <count($question["categories"]) ; $ii++){
            //
            $this->questioncol[$questioncount][] = "q".$questioncount."l".$i."c".$ii;
            $this->questionr[$questioncount][$i][$ii] = new votecount($question["ratings"]);
            $this->questionr[$questioncount][$i][$ii]->name = "q".$questioncount."l".$i."c".$ii;

          }
      }
    }
    $questioncount ++;
  }
}
  function insertrow($row){
    echo "<BR />inside insertrow <BR />";
    print_r($row);
    $questioncount = 0;
    foreach($this->structure as $question){

      if($question["type"] == "rate-categories"){
        for($i = 0 ; $i < count($question["lines"]) ; $i++){
          //
          for($ii = 0 ; $ii <count($question["categories"]) ; $ii++){
            //
            //$this->questioncol[$questioncount][] = "q".$questioncount."l".$i."c".$ii;
            $this->questionr[$questioncount][$i][$ii]->insertvote($row["q".$questioncount."l".$i."c".$ii]);
            echo "<BR />Line $i col $ii votesXX:";
            echo $this->questionr[$questioncount][$i][$ii]->value;
          }
      }
      }
      $questioncount ++;
    }

  }
  function GetResultsHTML(){
    return ResultHTML($this->structure,$this->questionr);
  }
  function printresults(){
    $questioncount = 0;
    foreach($this->structure as $question){
      echo "<BR />Question $questioncount";
      for($i = 0 ; $i < count($question["lines"]) ; $i++){
        //
        for($ii = 0 ; $ii <count($question["categories"]) ; $ii++){
          //
          //$this->questioncol[$questioncount][] = "q".$questioncount."l".$i."c".$ii;
          echo "<BR />Map ".$question["lines"][$i]." col $ii votes:";
          echo $this->questionr[$questioncount][$i][$ii]->value;
          //$this->questionr[$questioncount][$i][$ii]->insertvote($row["q".$questioncount."l".$i."c".$ii]);
        }
      }
      $questioncount ++;
    }
  }
}
 ?>
