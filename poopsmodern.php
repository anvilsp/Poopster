<?php
header("Content-type: application/json; charset=utf-8");
# get variable
if(isset($_GET["input"]) && !empty(trim($_GET['input']))) {
    $input = explode(" ", htmlentities(strtolower(trim($_GET["input"])))); # hopefully this is fine enough?
}
else    // no variable set, so null
{
    $input = NULL;
}
$contextmode = false;

print_r($input);

# WHAT DO WE DO WITH THE GET VARIABLE??
$games = array('dx', 'ro', 'tnr', 'splitz', 'snr', 'bg', 'monkey', 'indie');
if(isset($input)) {
    if(in_array($input[0], $games)) { 
        // specific games!
        //print("gaming\n");
    }
    if(in_array("createdby", $input)) { // display credit
        echo("Poopster created by @AnvilSP");
        exit();
    }
    if(in_array("help", $input)) {
        echo("Poopster combines two stage names from the following games: [dx] Deluxe [tnr] Touch & Roll [snr] Step & Roll [splitz] Banana Splitz [ro] Rolled Out [bg] Ballygon. 
        Use [context] to display full stage names, [monkey] to randomize only stages from Super Monkey Ball, or [indie] for only stages from indie games on the list. 
        [context] must either be the LAST parameter, or the ONLY parameter, for the desired behavior.");
        exit();
    }
    if(in_array("context", $input)) { // display stage context
        $contextmode = true;
        //print("context\n");
        if(count($input) == 1 || (count($input) > 1 && $input[0] == "context")) {
            $input = NULL;
        }
    }
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
if((isset($input) && in_array($input[0], array('dx', 'deluxe', 'mania', 'monkey'))) || !isset($input)) { // Super Monkey Ball Deluxe
    //print("loaded dx stages\n");
    $fullstages = append_names($fullstages, $paths['smbdx_full']);
    $firsthalves = append_names($firsthalves, $paths['smbdx_first']);
    $secondhalves = append_names($secondhalves, $paths['smbdx_second']);
}
if((isset($input) && in_array($input[0], array('tnr', 'touchandroll', 'monkey'))) || !isset($input)) { // Super Monkey Ball: Touch & Roll
    //print("loaded tnr stages\n");
    $fullstages = append_names($fullstages, $paths['tnr_full']);
    $firsthalves = append_names($firsthalves, $paths['tnr_first']);
    $secondhalves = append_names($secondhalves, $paths['tnr_second']);
}
if((isset($input) && in_array($input[0], array('snr', 'stepandroll', 'monkey'))) || !isset($input)) { // Super Monkey Ball: Step & Roll
    //print("loaded snr stages\n");
    $fullstages = append_names($fullstages, $paths['snr_full']);
    $firsthalves = append_names($firsthalves, $paths['snr_first']);
    $secondhalves = append_names($secondhalves, $paths['snr_second']);
}
if((isset($input) && in_array($input[0], array('splitz', 'monkey')) || !isset($input))) { // Super Monkey Ball: Banana Splitz
    //print("loaded splitz stages\n");
    $fullstages = append_names($fullstages, $paths['splitz_full']);
    $firsthalves = append_names($firsthalves, $paths['splitz_first']);
    $secondhalves = append_names($secondhalves, $paths['splitz_second']);
}
if((isset($input) && in_array($input[0], array('ro', 'rolledout', 'indie'))) || !isset($input)) { // Rolled Out!
    //print("loaded ro stages\n");
    $fullstages = append_names($fullstages, $paths['ro_full']);
    $firsthalves = append_names($firsthalves, $paths['ro_first']);
    $secondhalves = append_names($secondhalves, $paths['ro_second']);
}
if((isset($input) && in_array($input[0], array('bg', 'ballygon', 'indie'))) || !isset($input)) { // BALLYGON
    //print("loaded bg stages\n");
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