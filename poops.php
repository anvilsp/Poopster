<?php
/* Marble Roller stage name randomizer ("Poopster") created by AnvilSP
 * Inspired by the original randomizer command (rample/oopster) created by PetresInc
 */
header("Content-type: application/json; charset=utf-8");

define('LOGFILE', 'log/log.txt');
define('CENSORFILE', 'log/censor.txt');

$stagewords = ["-dx", "-ro", "-splitz", "-tnr", "-bg", "-snr", "-smba", "-smb3d", "-monkey", "-indie"];
$new_stagewords = ["-smba", "-smb3d"]; # these CANNOT be rolled with the -old flag enabled
$flagwords = ["-context", "-world", "-log", "-saved", "-old"];
$world_prefix = ["World", "Floor", "Stage"];

$stage_lists = [
    # key is game name, index 0 is the full stage name, index 1 is the first stage half, index 2 is the second stage half
    "dx" => ["stagename/smbdx/smbdx-stagename.txt", "stagename/smbdx/smbdx-firsthalf.txt", "stagename/smbdx/smbdx-secondhalf.txt"],
    "ro" => ["stagename/rolledout/ro-stagename.txt", "stagename/rolledout/ro-firsthalf.txt", "stagename/rolledout/ro-secondhalf.txt"],
    "ro057" => ["stagename/rolledout/ro057-stagename.txt", "stagename/rolledout/ro057-firsthalf.txt", "stagename/rolledout/ro057-secondhalf.txt"], # rolled out v0.5.7 stage list, for legacy seed purposes
    "tnr" => ["stagename/tnr/stagename.txt", "stagename/tnr/firsthalf.txt", "stagename/tnr/secondhalf.txt"],
    "snr" => ["stagename/snr/stagename.txt", "stagename/snr/firsthalf.txt", "stagename/snr/secondhalf.txt"],
    "splitz" => ["stagename/splitz/stagename.txt", "stagename/splitz/firsthalf.txt", "stagename/splitz/secondhalf.txt"], 
    "bg" => ["stagename/bg/stagename.txt", "stagename/bg/firsthalf.txt", "stagename/bg/secondhalf.txt"],
    "smb3d" => ["stagename/smb3d/stagename.txt", "stagename/smb3d/firsthalf.txt", "stagename/smb3d/secondhalf.txt"],
    "smba" => ["stagename/smba/stagename.txt", "stagename/smba/firsthalf.txt", "stagename/smba/secondhalf.txt"],
];

$error_messages = ["Filtered, please try a different seed!", "Sorry, can't say that one! Please reroll with a different seed.", "Bungled it! Please try another seed.", "oopsie teehee :3 please wewo with a different seed"];

$stage_firsthalf = []; # first half of stages; ie "Poops" in "Poops Table"
$stage_secondhalf = []; # second half of stages; ie "Table" in "Poops Table"
$stage_fullname = []; # full stage name; ie "Poops Table"
$stage_context = []; # game that stage comes from; dx, ro, splitz, etc.
$enabled_flags = []; # stagewords that were used, not counting flagwords; for logging purposes
$logged_stages = []; # a list of all of the stages that have currently been logged; populated if -log is called
$bad_filter = []; # a list of bad words or rolls that need to be killed; populated from file


if(isset($_GET['input'])) # check if any input has been provided
    $input = htmlentities($_GET['input']);
else
    $input = "";
if(isset($_GET['parse'])) # check if any parse method has been provided
    $parse = htmlentities($_GET['parse']);
else
    $parse = "";
if(isset($_GET['force'])) # check if "force" flag is specified; allows usage of 'force' command to bypass censor (if i implement it)
    $force = true;
else
    $force = false; # disable by default because i don't think any streamer would actually want this

# separate each word in the arguments into an array so that flags can be filtered out from the seed
$args = explode(" ", $input);
$check_arg = array_intersect($args, $stagewords); # check if any stage flags have been specified; all games will populate arrays if none are set

$enable_world = false; # flag for -world
$enable_context = false; # flag for -context
$enable_log = false; # flag for -log
$enable_force = false; # flag for ---force
$enable_logonly = false; # flag for -saved
$enable_old = false; # flag for -old
$censored = false; # flag for if the roll is censored

# CHECK INPUTS

if(count($args) == 1) # if there's exactly 1 argument
{
    # check if the arguments are the ones that should halt the program
    if($args[0] == "-createdby") # self plug
    {
        echo("Poopster created by @AnvilSP | https://anvilsp.com/poopster");
        exit();
    }
    else if ($args[0] == "-help") # help command
    {
        echo("Poopster combines two stage names from various marble rollers, including various entries from the Super Monkey Ball series, Rolled Out!, and BALLYGON. Use -games for the full list of games or -modifiers for a list of modifiers. | https://anvilsp.com/poopster");
        exit();
    }
    else if ($args[0] == "-games") # games help command
    {
        echo("[Super Monkey Ball] -dx (Deluxe/2/BM) | -tnr (Touch & Roll) | -snr (Step & Roll) | -splitz (Banana Splitz) | -smba (Adventure) | -smb3d (3D) || [Indies] -ro (Rolled Out!) | -bg (BALLYGON) || -monkey for only Monkey Ball stages, -indie for only indie stages [Parameters can be combined]");
        exit();
    }
    else if ($args[0] == "-modifiers") # modifiers help command
    {
        echo("MODIFIERS: -context (Display stage names), -world (Generate a stage number), -log (Save the output to the log file), -viewlog (View the log file), -old (Use legacy randomizer; pre 08/26/23)");
        exit();
    } else if ($args[0] == "-viewlog" || $args[0] == "-log") # view log command
    {
        echo("https://anvilsp.com/poopster/log");
        exit();
    }
}
if(in_array("-world", $args)) # set enable_world flag if the user has specified it
{
    $enable_world = true;
}
if(in_array("-context", $args)) # set enable_context flag if the user has specified it
{
    $enable_context = true;
}
if(in_array("-log", $args)){ # set enable_log flag if the user has specified it
    $enable_log = true;
}
if(in_array("-saved", $args)){ # set enable_logonly flag if the user has specified it
    $enable_logonly = true;
}
if(in_array("-old", $args)){ # set enable_old flag if the user has specified it
    $enable_old = true;
}
$extra_args = $args;
$final_seed = "";

# Weed out important flag words from potential seed
foreach($extra_args as $word => $entry) {
    if(in_array($entry, $stagewords) || in_array($entry, $flagwords) || $entry == basename(__FILE__))
    {
        if(!in_array($entry, $flagwords) or ($entry == "-old")) { # we preserve the "old" flag even though it's not a game
            array_push($enabled_flags, $entry);
        }
        unset($extra_args[$word]);
    }
}

# Generate potential seed string
foreach($extra_args as $word) {
    $final_seed = $final_seed.$word." ";
}

# If we have a seed, randomize based off of it
if(trim($final_seed) != ""){
    $rnd_seed = crc32($final_seed); # convert to integer with crc32 because php requires an int for some reason
    srand($rnd_seed);
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
    if($enable_old == true) { # append 0.5.7 stages if we're in legacy mode
        append_stages("ro057");
    }
    else {
        append_stages("ro");
    }
}
if(in_array("-bg", $args) || in_array("-indie", $args) || empty($check_arg)) {
    # BALLYGON; args: bg, indie
    append_stages("bg");
}
# v3: games that shouldn't be appended if -old is enabled
if($enable_old == false)
{
    if(in_array("-smb3d", $args) || in_array("-monkey", $args) || empty($check_arg)) {
        # Super Monkey Ball 3D; args: smb3d, monkey    
        append_stages("smb3d");
    }
    if(in_array("-smba", $args) || in_array("-monkey", $args) || empty($check_arg)) {
        # Super Monkey Ball Adventure; args: smba, monkey    
        append_stages("smba");
    }
}

# check if no stages have been appended
if(empty($stage_fullname)){
    print("Error");
    exit();
}

# check if "-old" is set and new games have been specified in the flags
if($enable_old and array_intersect($new_stagewords, $args)) {
    foreach($new_stagewords as $flag) {
        if(in_array($flag, $enabled_flags)) { # remove flags that aren't compatible for logging purposes
            $array_index = array_search($flag, $enabled_flags);
            unset($enabled_flags[$array_index]);
        }
    }
}

# Preliminary stuff is out of the way, this is where the magic happens

if(!$enable_logonly) # do default roll
    $final_stage = generate_stage();
else { # roll from the logged stages
    $logfile = file_get_contents(LOGFILE);
    $logged_stages = json_decode($logfile);
    $random_stage = rand(0, (count($logged_stages) - 1));
    $final_stage = json_decode(json_encode($logged_stages[$random_stage]), true);
}

# Check if the roll appears in the blacklist
$bad_filter = append_from_txt($bad_filter, CENSORFILE);
foreach($bad_filter as $censored_word) {
    $censored_word = trim($censored_word); # i actually hate this programming language
    if($final_stage['stagename'] == $censored_word) {
        $censored = true;
        break;
    }
}
if($censored) { # bad roll!
    if(trim($final_stage['seed']) == "") {
        # no seed detected; roll again
        $final_stage = generate_stage();
        $censored = false;
    }else{
        # display a random error message instead of the stage name
        srand();
        $final_stage['stagename'] = $error_messages[rand(0, (count($error_messages) - 1))];
    }
}

# DISPLAY RESULTS

if($parse == "web") { # web output, spit out the raw json
    print_r(json_encode($final_stage));
}
else{ # generic output, used for the nightbot command
    if($censored) # only display the stage name if it's censored, don't allow context (this still works on web tho)
        $final_string = $final_stage['stagename'];
    else{
        # everything is fine, proceed as normal
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
    }

    # print the final string
    print($final_string);

    if($enable_log)
        if(!$censored) # stage isn't censored so we can log it
            log_stage($final_stage);
        else # stage is censored so don't log it
            print(" | Logging error");

}

function generate_stage() { # Function to generate the stage
    global $stage_fullname, $stage_firsthalf, $stage_secondhalf, $stage_context, $final_seed, $enabled_flags;
    # Random generation
    $world = generate_world();
    $stage1 = rand(0, (count($stage_fullname) - 1));
    $stage2 = rand(0, (count($stage_fullname) - 1));
    # i probably should've made these objects instead of just storing them as arrays... next time maybe
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

function append_stages(string $game_name) { # automatically append the fullname, firsthalf, and second half for the given game
    global $stage_lists, $stage_fullname, $stage_firsthalf, $stage_secondhalf, $stage_context; # why do i gotta do that
    $append_fullname = append_with_name($stage_fullname, $stage_context, $stage_lists[$game_name][0], $game_name);
    $stage_fullname = $append_fullname[0];
    $stage_context = $append_fullname[1];
    $stage_firsthalf = append_from_txt($stage_firsthalf, $stage_lists[$game_name][1]);
    $stage_secondhalf = append_from_txt($stage_secondhalf, $stage_lists[$game_name][2]);
}

function generate_world() { # for if the 'world' flag is used, generate a stage number to go before the randomized name
    global $world_prefix;
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

function log_stage(array $stage) {
    # save the stage to the log
    global $logged_stages;
    if(trim($stage['seed']) == "") { # no seed was input, don't log
       return print(" | No seed found");
    }
    $logfile = file_get_contents(LOGFILE); # get log from file
    $logged_stages = json_decode($logfile); # populate array from json
    $already_in_array = false;
    # check if object is already in array
    if(!empty($logged_stages)) {
        foreach($logged_stages as $arrobj) {
            if($stage['stagename'] == $arrobj->stagename) { # if we find a match, change the flag and exit the loop
                $already_in_array = true;
                break;
            }
        }
    }
    if($already_in_array) { # don't log the duplicate
        return print(" | Already in log");
    }
    else { # not a duplicate, so we can log it
        if(empty($logged_stages)) {
            $logged_stages = array();
        }
        array_push($logged_stages, $stage);
        $logged_as_json = json_encode($logged_stages);
        file_put_contents(LOGFILE, $logged_as_json);
        return print(" | Added to log");
    }
}

function spacify($var) {
    return str_replace('_', ' ', $var);
}
?>