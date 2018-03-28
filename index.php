<?php
/************ example first *************/
include("cache-control.php");
$c = new Cache();

if(!$c->get("example", $result))
{
    $result = /* some extensive operation here */;
    $c->set("example", $result, 300); // cache result for 5 minutes (300 seconds)
}

var_dump($result);



/***************** example second *****************/
include("cache.php");

$c = new Cache();

if($c->get("views", $views))
{
    echo "Video views: " . $views;
    $c->remove("views"); // remove from cache
}
else
{
    echo "Cache not found or expired";
}
?>
