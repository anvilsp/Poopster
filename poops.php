<?php
header("Content-type: application/json; charset=utf-8");

$stagewords = ["dx", "ro", "splitz", "tnr", "bg", "snr", "monkey", "indie"];
$flagwords = ["context", "world"];
$world_prefix = ["World", "Floor", "Stage"];

$stage_lists = [
    # key is game name, index 0 is the full stage name, index 1 is the first stage half, index 2 is the second stage half
    "dx" => ["stagename/smbdx/smbdx-stagename.txt", "stagename/smbdx/smbdx-firsthalf.txt", "stagename/smbdx/smbdx-secondhalf.txt"],
    "ro" => ["stagename/rolledout/ro-stagename.txt", "stagename/rolledout/ro-firsthalf.txt", "stagename/rolledout/ro-secondhalf.txt"],
    "tnr" => ["stagename/tnr/stagename.txt", "stagename/tnr/firsthalf.txt", "stagename/tnr/secondhalf.txt"],
    "snr" => ["stagename/snr/stagename.txt", "stagename/snr/firsthalf.txt", "stagename/snr/secondhalf.txt"],
    "splitz" => ["stagename/splitz/stagename.txt", "stagename/splitz/firsthalf.txt", "stagename/splitz/secondhalf.txt"], 
    "bg" => ["stagename/bg/stagename.txt", "stagename/bg/firsthalf.txt", "stagename/bg/secondhalf.txt"]
];

$stage_firsthalf = [];
$stage_secondhalf = [];
$stage_fullname = [];

#$args = $argv;
$input = htmlentities($_GET['input']);
$args = explode(" ", $input);
$check_arg = array_intersect($args, $stagewords);

$enable_world = false;
$enable_context = false;

if(count($args) == 1) # if there's exactly 1 argument
{
    # check if the arguments are the ones that should halt the program
    if($args[0] == "createdby")
    {
        echo("Poopster created by @AnvilSP | https://anvilsp.com/poopster");
        exit();
    }
    else if ($args[0] == "help")
    {
        echo("Poopster combines two stage names from the following games (these can be used as parameters): [dx] Deluxe [tnr] Touch & Roll [snr] Step & Roll [splitz] Banana Splitz [ro] Rolled Out! [bg] BALLYGON. Use [world] to display a random stage number, [context] to display full stage names, [monkey] to randomize only stages from Super Monkey Ball, or [indie] for only stages from indie games on the list.");
        exit();
    }
}
if(in_array("world", $args))
{
    $enable_world = true;
}
if(in_array("context", $args))
{
    $enable_context = true;
}

$extra_args = $args;
$final_seed = "";

# Weed out important flag words from potential seed
foreach($extra_args as $word => $entry) {
    if(in_array($entry, $stagewords) || in_array($entry, $flagwords) || $entry == basename(__FILE__))
    {
        unset($extra_args[$word]);
    }
}

# print_r($extra_args);

# Generate potential seed string
foreach($extra_args as $word) {
    $final_seed = $final_seed.$word." ";
}
#print("seed: " . $final_seed);

# If we have a seed, randomize based off of it
if(trim($final_seed) != ""){
    #print("\nseed detected");
    $rnd_seed = crc32($final_seed);
    # print("\nconverted to " . $rnd_seed);
    srand($rnd_seed);
}

function append_from_txt($arr, $path) {
    # append from a text file to the chosen array
    $new = file($path);
    $arr = array_merge($arr, $new);
    return $arr;
}

function append_stages(string $game_name) {
    global $stage_lists, $stage_fullname, $stage_firsthalf, $stage_secondhalf; # why do i gotta do that
    $stage_fullname = append_from_txt($stage_fullname, $stage_lists[$game_name][0]);
    $stage_firsthalf = append_from_txt($stage_firsthalf, $stage_lists[$game_name][1]);
    $stage_secondhalf = append_from_txt($stage_secondhalf, $stage_lists[$game_name][2]);
}

function generate_world() {
    global $world_prefix;
    # for if the 'world' flag is used, generate a stage number to go before the randomized name
    $random_prefix = rand(0, 2); # 0 = World (SMB2 Story), 1 = Floor (SMB1), 2 = Stage (SMB2 Challenge)

    if($random_prefix == 0) {
        # if we roll World, sgenerate the stage number in the World format; up to 10-20 to fit SMBDX conventions
        $stagenumber = rand(1,10)."-".rand(1,20);
    }
    else {
        # if we're on a Floor or Stage, generate a number between 1 and 999
        $stagenumber = rand(1,999);
    }

    return $world_prefix[$random_prefix] . " " . $stagenumber;
}

# Append stages based on arguments
if(in_array("dx", $args) || in_array("monkey", $args) || empty($check_arg)) {
    # Super Monkey Ball Deluxe / Banana Mania / 2; args: dx, monkey    
    append_stages("dx");
}
if(in_array("tnr", $args) || in_array("monkey", $args) || empty($check_arg)) {
    # Super Monkey Ball: Touch & Roll; args: tnr, monkey    
    append_stages("tnr");
}
if(in_array("snr", $args) || in_array("monkey", $args) || empty($check_arg)) {
    # Super Monkey Ball: Step & Roll; args: snr, monkey    
    append_stages("snr");
}
if(in_array("splitz", $args) || in_array("monkey", $args) || empty($check_arg)) {
    # Super Monkey Ball: Banana Splitz; args: splitz, monkey    
    append_stages("splitz");
}
if(in_array("ro", $args) || in_array("indie", $args) || empty($check_arg)) {
    # Rolled Out!; args: ro, indie
    append_stages("ro");
}
if(in_array("bg", $args) || in_array("indie", $args) || empty($check_arg)) {
    # BALLYGON; args: bg, indie
    append_stages("bg");
}

# Random generation
$world = generate_world();
$stage1 = rand(0, (count($stage_fullname) - 1));
$stage2 = rand(0, (count($stage_fullname) - 1));
$final_string = "";

# append world if it's called for
if($enable_world) {
    $final_string = $final_string . $world . " - ";
}

$final_string = $final_string . spacify(trim($stage_firsthalf[$stage1]) . trim($stage_secondhalf[$stage2]));

# append the stage context if it's called for
if($enable_context){
    $final_string = $final_string . ";[" . spacify(trim($stage_fullname[$stage1])) . " and " . spacify(trim($stage_fullname[$stage2])) . "]";
}

# print the final string
print($final_string);

function spacify($var) {
    return str_replace('_', ' ', $var);
}
?>