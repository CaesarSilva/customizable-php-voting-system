<?php
$r = [];
$r[0] = [];
$r[0]["type"] = "rate-categories";
$r[0]["question"] = "First question";
$r[0]["lines"] = ["map1", "map2" , "map3"];
$r[0]["categories"] = ["how good it is", "how good the map maker is", "put a random number"];
$r[0]["ratings"] = ["0","1","2","3","4","5"];


$r[1] = [];
$r[1]["type"] = "rate-categories";
$r[1]["question"] = "second question";
$r[1]["lines"] = ["map1", "map2" , "map3"];
$r[1]["categories"] = ["how good it is", "how good the map maker is", "put a random number"];
$r[1]["ratings"] = ["0","1","2","3","4","5"];
$jsontext = json_encode($r);
echo $jsontext;
echo "</BR></BR>";
$f = json_decode($jsontext);
$jsontext1 = json_encode($r);
echo $jsontext1;
?>
