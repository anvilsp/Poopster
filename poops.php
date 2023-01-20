<?php
header("Content-type: text");
ini_set('display_errors', 1);

# get variable
if(isset($_GET["input"])) {
    $input = htmlentities($_GET["input"]); # hopefully this is fine enough?
}
else    // no variable set, so null
{
    $input = NULL;
}
$contextmode = false;

# WHAT DO WE DO WITH THE GET VARIABLE??
$games = array('dx', 'ro', 'tnr', 'splitz');
if(in_array($input, $games)) { 
    // specific games!
}
else if($input == "createdby") { // display credit
    echo("Poopster created by @AnvilSP");
    exit();
}
else if($input == "context") { // display stage context
    $contextmode = true;
    $input = NULL;
}
else {
    $input = NULL;
}

# DEFINE ARRAYS
$firsthalves = array();     // array of first stage name halves
$secondhalves = array();    // array of second stage name halves
$fullstages = array();      // array of full stage names

# DEFINE PATHS
$paths = [
    'smbdx_full' => 'stagename/smbdx/smbdx-stagename.txt',
    'smbdx_first' => 'stagename/smbdx/smbdx-firsthalf.txt',
    'smbdx_second' => 'stagename/smbdx/smbdx-secondhalf.txt',
    'ro_full' => 'stagename/rolledout/ro-stagename.txt',
    'ro_first' => 'stagename/rolledout/ro-firsthalf.txt',
    'ro_second' => 'stagename/rolledout/ro-secondhalf.txt',
    'tnr_full' => 'stagename/tnr/stagename.txt',
    'tnr_first' => 'stagename/tnr/firsthalf.txt',
    'tnr_second' => 'stagename/tnr/secondhalf.txt'
];

// append text files to array
if($input == "dx" || !$input) {
    //print("parse dx stages<br>");
    $fullstages = append_names($fullstages, $paths['smbdx_full']);
    $firsthalves = append_names($firsthalves, $paths['smbdx_first']);
    $secondhalves = append_names($secondhalves, $paths['smbdx_second']);
    //print_r($fullstages);
    //print(count($fullstages));
}
if($input == "ro" || !$input) {
    //print("<br>parse rolled out stages<br>");
    $fullstages = append_names($fullstages, $paths['ro_full']);
    $firsthalves = append_names($firsthalves, $paths['ro_first']);
    $secondhalves = append_names($secondhalves, $paths['ro_second']);
    //print_r($fullstages);
    //print(count($fullstages));
}
if($input == "tnr" || !$input) {
    //print("<br>parse touch and roll stages");
    $fullstages = append_names($fullstages, $paths['tnr_full']);
    $firsthalves = append_names($firsthalves, $paths['tnr_first']);
    $secondhalves = append_names($secondhalves, $paths['tnr_second']);
}
if($input == "splitz" || !$input) {
    //print("parse splitz stages");
}

# once everything has been appended, pick stages

if(!count($fullstages))
{
    echo("[Error]");
    exit();
}

# first half
$rng1 = rand(0, count($fullstages));
$selected1 = $firsthalves[$rng1];
$full1 = $fullstages[$rng1];

# determine second half
$rng2 = rand(0, count($fullstages));
$selected2 = $secondhalves[$rng2];
$full2 = $fullstages[$rng2];

$generatedPoops = spacify(trim($selected1).trim($selected2));
print($generatedPoops);
if($contextmode) {
    print(";(");
    print(spacify(trim($full1))." and ".spacify(trim($full2)));
    print(")");
}

function append_names($arr, $path) {
    $new = file($path);
    $arr = array_merge($arr, $new);
   return $arr;
}

function spacify($var) {
    return str_replace('_', ' ', $var);
}
?>