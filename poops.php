<?php
/* Marble Roller stage name randomizer ("Poopster") created by AnvilSP
 * v5: New stages, logging overhaul
 * Based on the original randomizer command by PetresInc
 * BALLYGON and Banana Rumble stage lists based on IL sheets by Dead Line
 * Shoutout to Helix13_, StevenCW_, iswimfly, Dead Line, SoyMalkLatte and anyone who helped with or inspired this project in some way 💜
 */
header("Content-type: application/json; charset=utf-8");

define('LOGFILE', 'log/log.txt');
define('CENSORFILE', 'log/censor.txt');
define('LASTFILE', 'log/lastfile.txt');

class Stage { # class for generated stage objects; replacement for old non-classed arrays
    public $stagename;
    public $first_stage;
    public $second_stage;
    public $first_context;
    public $second_context;
    public $world;
    public $flags;
    public $seed;

    function __construct($name, $stage1, $stage2, $context1, $context2, $world, $flags, $seed) {
        $this->stagename = $name;
        $this->first_stage = $stage1;
        $this->second_stage = $stage2;
        $this->first_context = $context1;
        $this->second_context = $context2;
        $this->world = $world;
        $this->flags = $flags;
        $this->seed = $seed;
    }

    function check_shiny() { # check if the two stages are the exact same entry
        if($this->first_stage == $this->second_stage && $this->first_context == $this->second_context){
            return true;
        }
        return false;
    }

    function get_result($world, $context) { # return the stage name with optional flags
        $final_string = '';
        if($world) {
            $final_string = $final_string . $this->world . ' ~ ';
        }
        $final_string = $final_string . $this->stagename . ($this->check_shiny() ? ' ⭐' : '');
        if($context) {
            $final_string = $final_string . " (" . $this->first_stage . " [" . $this->first_context . "] + " . $this->second_stage . " [" . $this->second_context . "])";
        }
        return $final_string;
    }

    function get_json() { # return the raw output for web parsing
        return json_encode($this);
    }
}

$stagewords = ["-dx", "-ro", "-splitz", "-tnr", "-bg", "-snr", "-smba", "-smb3d", "-monkey", "-indie", "-soup", "-br"];
$new_stagewords_v4 = ["-smba", "-smb3d"]; # cannot be rolled in v3
$new_stagewords_v5 = ["-br"]; # cannot be rolled in v3 or v4
$flagwords = ["-context", "-world", "-log", "-saved", "-old", "-old:v3", "-old:v4"];
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
    "br" => ["stagename/br/stagename.txt", "stagename/br/firsthalf.txt", "stagename/br/secondhalf.txt"],
    "soup" => ["stagename/soup/stagename.txt", "stagename/soup/firsthalf.txt", "stagename/soup/secondhalf.txt"]
];

$error_messages = [
    "Filtered, please try a different seed!",
    "Sorry, can't say that one! Please reroll with a different seed.",
    "Bungled it! Please try another seed.",
    "oopsie teehee :3 please wewo with a different seed",
    "it's so bananover. try a different seed."
];

$stage_firsthalf = []; # first half of stages; ie "Poops" in "Poops Table"
$stage_secondhalf = []; # second half of stages; ie "Table" in "Poops Table"
$stage_fullname = []; # full stage name; ie "Poops Table"
$stage_context = []; # game that stage comes from; dx, ro, splitz, etc.
$enabled_flags = []; # stagewords that were used, not counting flagwords; for logging purposes
#$logged_stages = []; # a list of all of the stages that have currently been logged; populated if -log is called
$bad_filter = []; # a list of bad words or rolls that need to be killed; populated from file

if(isset($_GET['input'])) # check if any input has been provided
    $input = htmlentities($_GET['input']);
else
    $input = "";
if(isset($_GET['parse'])) # check if any parse method has been provided
    $parse = htmlentities($_GET['parse']);
else
    $parse = "";

# separate each word in the arguments into an array so that flags can be filtered out from the seed
$args = explode(" ", $input);
$check_arg = array_intersect($args, $stagewords); # check if any stage flags have been specified; all games will populate arrays if none are set

$enable_world = false; # flag for -world
$enable_context = false; # flag for -context
$enable_log = false; # flag for -log
$enable_logonly = false; # flag for -saved
$enable_v3 = false; # flag for -old:v3
$enable_v4 = false; # flag for -old, -old:v4
$censored = false; # flag for if the roll is censored

# CHECK INPUTS

if(count($args) == 1) # if there's exactly 1 argument
{
    # check if the arguments are the ones that should halt the program
    if($args[0] == "-createdby") # self plug
    {
        echo("Poopster created by @AnvilSP | https://poopster.anvilsp.com");
        exit();
    }
    else if ($args[0] == "-help") # help command
    {
        echo("Poopster combines two stage names from various marble rollers, including various entries from the Super Monkey Ball series, Rolled Out!, and BALLYGON. Use -games for the full list of games or -modifiers for a list of modifiers. | https://poopster.anvilsp.com");
        exit();
    }
    else if ($args[0] == "-games") # games help command
    {
        echo("[SMB] -dx (Deluxe/2/BM) | -tnr (Touch & Roll) | -snr (Step & Roll) | -splitz (Banana Splitz) | -smba (Adventure) | -smb3d (3D) | -br (Banana Rumble) || [Indies] -ro (Rolled Out!) | -bg (BALLYGON) || -monkey for only Monkey Ball stages, -indie for only indie stages [Parameters can be combined]");
        exit();
    }
    else if ($args[0] == "-modifiers") # modifiers help command
    {
        echo("MODIFIERS: -context (Display stage names), -world (Generate a stage number), -log (Save the output to the log file), -viewlog (View the log file), -saved (Display a logged stage), -old:v3 (Legacy randomizer; pre 08-2023), -old:v4 (Legacy randomizer; pre 07-2024)");
        exit();
    } else if ($args[0] == "-viewlog" || $args[0] == "-log") # view log command
    {
        echo("https://poopster.anvilsp.com/log");
        exit();
    } else if($args[0] == "-spreadsheet") {
        echo("https://docs.google.com/spreadsheets/d/1nI0rKA26-tE7wKC8jB8JtYDAbNwEFRfz5u6y7PLqFd4/edit?usp=sharing");
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
if(in_array("-old", $args) || in_array("-old:v4", $args)) { # set enable_v4 flag if user has specified it
    $enable_v4 = true;
}
else if(in_array("-old:v3", $args)){ # set enable_v3 flag if the user has specified it
    $enable_v3 = true;
}
$extra_args = $args;
$final_seed = "";

# Weed out important flag words from potential seed
foreach($extra_args as $word => $entry) {
    if(in_array($entry, $stagewords) || in_array($entry, $flagwords) || $entry == basename(__FILE__))
    {
        if(!in_array($entry, $flagwords) or ($entry == "-old:v4" or $entry == "-old" or ($entry == "-old:v3" and !$enable_v4))) { # we preserve the "old" flag even though it's not a game
            if($entry == "-old" && $enable_v4) # convert -old to -old:v4 in log for future reference
                array_push($enabled_flags, "-old:v4");
            else
                array_push($enabled_flags, $entry);
        }
        unset($extra_args[$word]);
    }
}

# Generate potential seed string
if(count(array_filter($extra_args)) == 0) {
    # create a timestamp-based seed if nothing was entered
    $now = DateTime::createFromFormat('U.u', microtime(true));
    $final_seed = $now->format("mDyHisu")." "; # it needs the space at the end or else it won't work
} else {
    foreach($extra_args as $word) {
        $final_seed = $final_seed.$word." ";
    }
}

# If we have a seed, randomize based off of it
if(trim($final_seed) != ""){
    # WARNING: DO ---NOT--- ADD TRIM TO THE SEED OR ELSE IT RUINS COMPATIBILITY
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
    if($enable_v3 == true) { # append 0.5.7 stages if we're in legacy mode
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
# v4: games that shouldn't be appended if -old:v3 is enabled
if(!$enable_v3)
{
    if(in_array("-smb3d", $args) || in_array("-monkey", $args) || empty($check_arg)) {
        # Super Monkey Ball 3D; args: smb3d, monkey    
        append_stages("smb3d");
    }
    if(in_array("-smba", $args) || in_array("-monkey", $args) || empty($check_arg)) {
        # Super Monkey Ball Adventure; args: smba, monkey    
        append_stages("smba");
    }
    if(!$enable_v4) { # v5: games that shouldn't be appended if -old:v4 is enabled
        if(in_array("-br", $args) || in_array("-monkey", $args) || empty($check_arg)) {
            # Super Monkey Ball Banana Rumble; args: br, monkey
            append_stages("br");
        }
    }
    if(in_array("-soup", $args)) {
        # Soupster
        append_stages("soup");
    }
}

# check if no stages have been appended
if(empty($stage_fullname)){
    print("Error - no stages populated!");
    exit();
}

# remove stage words that aren't compatible with the current version
if(($enable_v3 or $enable_v4) and (array_intersect($new_stagewords_v4, $args) or array_intersect($new_stagewords_v5, $args))) {
    if($enable_v3) { # don't remove these on a v4 save
        foreach($new_stagewords_v4 as $flag) {
            if(in_array($flag, $enabled_flags)) {
                $array_index = array_search($flag, $enabled_flags);
                unset($enabled_flags[$array_index]);
            }
        }
    }
    foreach($new_stagewords_v5 as $flag) {
        if(in_array($flag, $enabled_flags)) {
            $array_index = array_search($flag, $enabled_flags);
            unset($enabled_flags[$array_index]);
        }
    }
}

# Preliminary stuff is out of the way, this is where the magic happens

if(!$enable_logonly) # do default roll
    $final_stage = generate_stage();
else { # roll from the logged stages
    try {
        $db = new SQLite3('poopster.sqlite', SQLITE3_OPEN_READONLY);
        if(count($extra_args) > 0 and is_numeric($extra_args[1])) {
            # search by id if the input is something like "-saved 1"
            $random_query = $db->query("SELECT * FROM saved WHERE id = ". $extra_args[1] ." LIMIT 1");
        } else {
            # get a random result
            $random_query = $db->query("SELECT * FROM saved ORDER BY RANDOM() LIMIT 1");
        }
    
        # put the stage into an array
        $random_stage = $random_query->fetchArray(SQLITE3_ASSOC);
        if(!$random_stage) { # there's no stage so we kill
            print("No stage found.");
            exit();
        }
        $final_stage = new Stage(
            $random_stage['stagename'],
            $random_stage['first_stage'],
            $random_stage['second_stage'],
            $random_stage['first_context'],
            $random_stage['second_context'],
            $random_stage['world'],
            $random_stage['flags'],
            $random_stage['seed']
        );
    }
    catch(Exception $e) { # database file missing or something
        if($parse != "web")
        {
            print("No database found. | ");
        }
        $final_stage = generate_stage();
    }
}

# Check if the roll appears in the blacklist
$bad_filter = append_from_txt($bad_filter, CENSORFILE);
foreach($bad_filter as $censored_word) {
    $censored_word = trim($censored_word); # i actually hate this programming language
    if($final_stage->stagename == $censored_word) {
        $censored = true;
        break;
    }
}

if($censored) { # bad roll!
    if(trim($final_stage->seed) == "") {
        # no seed detected; roll again
        $final_stage = generate_stage();
        $censored = false;
    }else{
        # display a random error message instead of the stage name
        srand();
        $final_stage->stagename = $error_messages[rand(0, (count($error_messages) - 1))];
    }
}

# DISPLAY RESULTS

if($parse == "web") { # web output, spit out the raw json
    print_r($final_stage->get_json());
}
else{ # generic output, used for the nightbot command
    if($censored) # only display the stage name if it's censored, don't allow context (this still works on web tho)
        $final_string = $final_stage->stagename;
    else{ 
        $final_string = $final_stage->get_result($enable_world, $enable_context);
    }

    # print the final string
    print($final_string);

    if($enable_log & (count(array_filter($extra_args)) != 0))
    {
        if(!$censored) # stage isn't censored so we can log it
            log_stage($final_stage);
        else # stage is censored so don't log it
            print(" | Logging error");
    }
    else if((count(array_filter($extra_args)) == 0)) {
        last_file($final_stage);
    }

}

function generate_stage() { # Function to generate the stage
    global $stage_fullname, $stage_firsthalf, $stage_secondhalf, $stage_context, $final_seed, $enabled_flags;
    # Random generation
    $world = generate_world();
    $stage1 = rand(0, (count($stage_fullname) - 1));
    $stage2 = rand(0, (count($stage_fullname) - 1));
    # i probably should've made these objects instead of just storing them as arrays... next time maybe
    # get owned idiot they're objects now
    $final_stage = new Stage(
        spacify(trim($stage_firsthalf[$stage1]) . trim($stage_secondhalf[$stage2])),
        spacify(trim($stage_fullname[$stage1])),
        spacify(trim($stage_fullname[$stage2])),
        $stage_context[$stage1],
        $stage_context[$stage2],
        $world,
        $enabled_flags,
        isset($final_seed) ? trim($final_seed) : null
    );
    return $final_stage;
}

function append_from_txt($arr, $path) {
    # append from a text file to the chosen array
    $new = file($path);
    $arr = array_merge($arr, $new);
    return $arr;
}

function append_with_name($arr1, $arr2, $path, $name) {
    # append stagename with game name for context; returns an array of arrays (0 = stagenames, 1 = context)
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
    # append the fullnames and contexts
    $append_fullname = append_with_name($stage_fullname, $stage_context, $stage_lists[$game_name][0], $game_name);
    $stage_fullname = $append_fullname[0];
    $stage_context = $append_fullname[1];
    # append firsthalves
    $stage_firsthalf = append_from_txt($stage_firsthalf, $stage_lists[$game_name][1]);
    # append secondhalves
    $stage_secondhalf = append_from_txt($stage_secondhalf, $stage_lists[$game_name][2]);
}

function generate_world() { # for if the 'world' flag is used, generate a stage number to go before the randomized name
    global $world_prefix, $enable_v3, $enable_v4;
    $random_prefix = rand(0, 2); # 0 = World, 1 = Floor (SMB1), 2 = Stage (SMB2 Challenge)

    if($random_prefix == 0) {
        # if we roll World, generate the stage number in the World format
        if(!$enable_v3 && !$enable_v4) {
            # IMPORTANT: when adding new randomizers, make them version-specific or else it'll break old results
            $is_ex = rand(0,1);
            if($is_ex) { # World 10 EX-2 - They
                $stagenumber = rand(1,10)." EX-".rand(1,10);
            } else { # World 6-9 - Sticky Time
                $stagenumber = rand(1,10)."-".rand(1,20);
            }
        } else {
            $stagenumber = rand(1,10)."-".rand(1,20);
        }
    }
    else {
        # if we're on a Floor or Stage, generate a number between 1 and 999
        $stagenumber = rand(1,999);
    }

    return $world_prefix[$random_prefix] . " " . $stagenumber;
}

function log_stage(Stage $stage) {
    try {
        # create the database if it doesn't already exist
        $db = new SQLite3('poopster.sqlite', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
        $db->enableExceptions(true);
        $db->query('CREATE TABLE IF NOT EXISTS "saved" (
            "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            "stagename" TEXT,
            "first_stage" TEXT,
            "second_stage" TEXT,
            "first_context" TEXT,
            "second_context" TEXT,
            "world" TEXT,
            "flags" TEXT,
            "seed" TEXT
        )');
    
        # check if the stage is already in the log
        $check_if_exists = $db->query('SELECT id FROM saved WHERE stagename="'.htmlentities($stage->stagename).'" AND seed = "'.$stage->seed.'" LIMIT 1');
        $existing_entry = $check_if_exists->fetchArray(SQLITE3_ASSOC);
        if($existing_entry === false) {
            # it's not in the log, so we add it!
            $db->exec('BEGIN');
            $db->query('INSERT INTO saved (stagename, first_stage, second_stage, first_context, second_context, world, flags, seed)
            VALUES ("'.htmlentities($stage->stagename, ENT_QUOTES, 'UTF-8').'","'.htmlentities($stage->first_stage, ENT_QUOTES, 'UTF-8').'",
                "'.htmlentities($stage->second_stage, ENT_QUOTES, 'UTF-8').'","'.htmlentities($stage->first_context, ENT_QUOTES, 'UTF-8').'","'.htmlentities($stage->second_context, ENT_QUOTES, 'UTF-8').'",
                "'.htmlentities($stage->world, ENT_QUOTES, 'UTF-8').'","'.htmlentities(json_encode($stage->flags), ENT_QUOTES, 'UTF-8').'","'.$stage->seed.'")'
            );
            $db->exec('COMMIT');
            return print(" | Added entry #".$db->lastInsertRowID()." to log");
        }
        else {
            # already in the log, be helpful and give the id
            return print(" | Already in log - Entry #".$existing_entry['id']);
        }
    }
    catch(Exception $e) {
        # this probably only occurs if you like don't have sqlite or something
        return print(" | An error occurred.");
    }
}

function last_file(Stage $stage) {
    # saves the last 100 'non-seeded' rolls
    $lastfile = file_get_contents(LASTFILE);
    $last_stages = json_decode($lastfile);
    if(json_last_error() !== JSON_ERROR_NONE) {
        $last_stages = [];
    }
    $last_stages = array_slice($last_stages, 0, 100);
    array_unshift($last_stages, $stage);
    $last_json = json_encode($last_stages);
    file_put_contents(LASTFILE, $last_json);
}

function spacify($var) {
    return str_replace('_', ' ', $var);
}
?>