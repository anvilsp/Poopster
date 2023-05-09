<?php
header("Content-type: application/json; charset=utf-8");

$stagewords = ["-dx", "-ro", "-splitz", "-tnr", "-bg", "-snr", "-monkey", "-indie"];
$flagwords = ["-context", "-world", "-log"];
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
$stage_context = [];
$enabled_flags = [];

#$args = $argv;
if(isset($_GET['input']))
    $input = htmlentities($_GET['input']);
else
    $input = "";
if(isset($_GET['parse']))
    $parse = htmlentities($_GET['parse']);
else
    $parse = "";
$args = explode(" ", $input);
$check_arg = array_intersect($args, $stagewords);

$enable_world = false;
$enable_context = false;
$enable_log = false;

if(count($args) == 1) # if there's exactly 1 argument
{
    # check if the arguments are the ones that should halt the program
    if($args[0] == "-createdby")
    {
        echo("Poopster created by @AnvilSP | https://anvilsp.com/poopster");
        exit();
    }
    else if ($args[0] == "-help")
    {
        echo("Poopster combines two stage names from various marble rollers, including various entries from the Super Monkey Ball series, Rolled Out!, and BALLYGON. Use -games for the full list of games or -modifiers for a list of modifiers. | https://anvilsp.com/poopster");
        exit();
    }
    else if ($args[0] == "-games")
    {
        echo("[Super Monkey Ball] -dx (Deluxe/2/BM) | -tnr (Touch & Roll) | -snr (Step & Roll) | -splitz (Banana Splitz) || [Indies] -ro (Rolled Out!) | -bg (BALLYGON) || -monkey for only Monkey Ball stages, -indie for only indie stages [Parameters can be combined]");
        exit();
    }
    else if ($args[0] == "-modifiers")
    {
        echo("MODIFIERS: -context (Display stage names), -world (Generate a stage number), -log (Save the output to the log file), -viewlog (View the log file)");
        exit();
    } else if ($args[0] == "-viewlog")
    {
        echo("https://anvilsp.com/poopster/log.txt");
        exit();
    }
}
if(in_array("-world", $args))
{
    $enable_world = true;
}
if(in_array("-context", $args))
{
    $enable_context = true;
}
if(in_array("-log", $args)){
    $enable_log = true;
}

$extra_args = $args;
$final_seed = "";

# Weed out important flag words from potential seed
foreach($extra_args as $word => $entry) {
    if(in_array($entry, $stagewords) || in_array($entry, $flagwords) || $entry == basename(__FILE__))
    {
        if(!in_array($entry, $flagwords)) {
            array_push($enabled_flags, $entry);
        }
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

function append_with_name($arr1, $arr2, $path, $name) {
    # append stagename with game name for context
    if($file = fopen($path, "r")) {
        while(!feof($file)) {
            $line = fgets($file);
            # append to one array
            array_push($arr1, $line);
            # append to the other array
            array_push($arr2, $name);
        }
        fclose($file);
        return array($arr1, $arr2);
    }
}

function append_stages(string $game_name) {
    global $stage_lists, $stage_fullname, $stage_firsthalf, $stage_secondhalf, $stage_context; # why do i gotta do that
    $append_fullname = append_with_name($stage_fullname, $stage_context, $stage_lists[$game_name][0], $game_name);
    $stage_fullname = $append_fullname[0];
    $stage_context = $append_fullname[1];
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
if(in_array("-dx", $args) || in_array("-monkey", $args) || empty($check_arg)) {
    # Super Monkey Ball Deluxe / Banana Mania / 2; args: dx, monkey    
    append_stages("dx");
}
if(in_array("-tnr", $args) || in_array("-monkey", $args) || empty($check_arg)) {
    # Super Monkey Ball: Touch & Roll; args: tnr, monkey    
    append_stages("tnr");
}
if(in_array("-snr", $args) || in_array("-monkey", $args) || empty($check_arg)) {
    # Super Monkey Ball: Step & Roll; args: snr, monkey    
    append_stages("snr");
}
if(in_array("-splitz", $args) || in_array("-monkey", $args) || empty($check_arg)) {
    # Super Monkey Ball: Banana Splitz; args: splitz, monkey    
    append_stages("splitz");
}
if(in_array("-ro", $args) || in_array("-indie", $args) || empty($check_arg)) {
    # Rolled Out!; args: ro, indie
    append_stages("ro");
}
if(in_array("-bg", $args) || in_array("-indie", $args) || empty($check_arg)) {
    # BALLYGON; args: bg, indie
    append_stages("bg");
}

function generate_stage() {
    global $stage_fullname, $stage_firsthalf, $stage_secondhalf, $stage_context, $final_seed, $enabled_flags;
    # Random generation
    $world = generate_world();
    $stage1 = rand(0, (count($stage_fullname) - 1));
    $stage2 = rand(0, (count($stage_fullname) - 1));
    $final_array = array(
        "stagename" => spacify(trim($stage_firsthalf[$stage1]) . trim($stage_secondhalf[$stage2])),
        "first_stage" => spacify(trim($stage_fullname[$stage1])),
        "second_stage" => spacify(trim($stage_fullname[$stage2])),
        "first_context" => $stage_context[$stage1],
        "second_context" => $stage_context[$stage2],
        "world" => $world,
        "flags" => $enabled_flags,
        "seed" => isset($final_seed) ? trim($final_seed) : null
    );
    return $final_array;
}

$final_stage = generate_stage();

if($parse == "web") {
    print_r(json_encode($final_stage));
}
else{
    $final_string = "";

    # append world if it's called for
    if($enable_world) {
        $final_string = $final_string . $final_stage['world'] . " - ";
    }

    $final_string = $final_string . $final_stage['stagename'];

    # append the stage context if it's called for
    if($enable_context){
        $final_string = $final_string . " (" . $final_stage['first_stage'] . " [" . $final_stage['first_context'] 
        . "] and " . $final_stage['second_stage'] . " [" . $final_stage['second_context'] . "])";
    }

    if($enable_log) {
        $store_str = "!poopster";
        if(!empty($final_stage['flags'])) {
            foreach($final_stage['flags'] as $word)
            {
                $store_str = trim($store_str . " " . $word);
            }
        }
        if($final_stage['seed'] != "")
            $store_str = $store_str . " " . $final_stage['seed'] . " = " . $final_stage['stagename'];
        else
            $store_str = $final_stage['stagename'];
        $log_file = fopen("log.txt", "a") or die("Could not find log file!");
        fwrite($log_file, "\n".$store_str);
        fclose($log_file);
    }

    # print the final string
    print($final_string);
}

function spacify($var) {
    return str_replace('_', ' ', $var);
}
?>