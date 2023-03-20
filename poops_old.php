<?php
header("Content-type: application/json; charset=utf-8");
# get variable
if(isset($_GET["input"])) {
    $input = htmlentities(strtolower($_GET["input"])); # hopefully this is fine enough?
}
else    // no variable set, so null
{
    $input = NULL;
}
$contextmode = false;

# WHAT DO WE DO WITH THE GET VARIABLE??
$games = array('dx', 'ro', 'tnr', 'splitz', 'snr', 'bg', 'monkey', 'indie');
if(in_array($input, $games)) { 
    // specific games!
}
else if($input == "createdby") { // display credit
    echo("Poopster created by @AnvilSP");
    exit();
}
else if($input == "help") {
    echo("Poopster combines two stage names from the following games (these can be used as parameters): [dx] Deluxe [tnr] Touch & Roll [snr] Step & Roll [splitz] Banana Splitz [ro] Rolled Out [bg] Ballygon. 
    Use [context] to display full stage names, [monkey] to randomize only stages from Super Monkey Ball, or [indie] for only stages from indie games on the list. 
    (Parameters can't be combined)");
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
    'tnr_second' => 'stagename/tnr/secondhalf.txt',
    'snr_full' => 'stagename/snr/stagename.txt',
    'snr_first' => 'stagename/snr/firsthalf.txt',
    'snr_second' => 'stagename/snr/secondhalf.txt',
    'splitz_full' => 'stagename/splitz/stagename.txt',
    'splitz_first' => 'stagename/splitz/firsthalf.txt',
    'splitz_second' => 'stagename/splitz/secondhalf.txt',
    'bg_full' => 'stagename/bg/stagename.txt',
    'bg_first' => 'stagename/bg/firsthalf.txt',
    'bg_second' => 'stagename/bg/secondhalf.txt'
];

// append text files to array
if($input == "dx" || $input == "monkey" || !$input) { // Super Monkey Ball Deluxe
    $fullstages = append_names($fullstages, $paths['smbdx_full']);
    $firsthalves = append_names($firsthalves, $paths['smbdx_first']);
    $secondhalves = append_names($secondhalves, $paths['smbdx_second']);
}
if($input == "tnr" || $input == "monkey" || !$input) { // Super Monkey Ball: Touch & Roll
    $fullstages = append_names($fullstages, $paths['tnr_full']);
    $firsthalves = append_names($firsthalves, $paths['tnr_first']);
    $secondhalves = append_names($secondhalves, $paths['tnr_second']);
}
if($input == "snr" || $input == "monkey" || !$input) { // Super Monkey Ball: Step & Roll
    $fullstages = append_names($fullstages, $paths['snr_full']);
    $firsthalves = append_names($firsthalves, $paths['snr_first']);
    $secondhalves = append_names($secondhalves, $paths['snr_second']);
}
if($input == "splitz" || $input == "monkey" || !$input) { // Super Monkey Ball: Banana Splitz
    $fullstages = append_names($fullstages, $paths['splitz_full']);
    $firsthalves = append_names($firsthalves, $paths['splitz_first']);
    $secondhalves = append_names($secondhalves, $paths['splitz_second']);
}
if($input == "ro" || $input == "indie" || !$input) { // Rolled Out!
    $fullstages = append_names($fullstages, $paths['ro_full']);
    $firsthalves = append_names($firsthalves, $paths['ro_first']);
    $secondhalves = append_names($secondhalves, $paths['ro_second']);
}
if($input == "bg" || $input == "indie" || !$input) { // BALLYGON
    $fullstages = append_names($fullstages, $paths['bg_full']);
    $firsthalves = append_names($firsthalves, $paths['bg_first']);
    $secondhalves = append_names($secondhalves, $paths['bg_second']);
}

# once everything has been appended, pick stages

if(!count($fullstages))
{
    echo("[Error]");
    exit();
}

# first half
$rng1 = rand(0, count($fullstages) - 1);
$selected1 = $firsthalves[$rng1];
$full1 = $fullstages[$rng1];

# determine second half
$rng2 = rand(0, count($fullstages) - 1);
$selected2 = $secondhalves[$rng2];
$full2 = $fullstages[$rng2];

$generatedPoops = spacify(trim($selected1).trim($selected2));
print($generatedPoops);
if($contextmode) {
    print(";[");
    print(spacify(trim($full1))." and ".spacify(trim($full2)));
    print("]");
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