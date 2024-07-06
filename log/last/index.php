<!DOCTYPE html>
<html>
<head>
        <title>Last Rolls | Poopster</title>
        <link rel="stylesheet" href="../../style.css">
        <meta name="viewport" content="width=device-width">
    </head>
    <body>
        <div class="footer">
            <a href="../..">Back</a> | <a href="../../log">Back to Log</a>
            <br>
        </div>
        <div class="savedBlock">
        <h2>Last 100 Rolls</h2>
            <?php
                try {
                    $log_file = file_get_contents("../lastfile.txt");
                    $decode_log = json_decode($log_file);
                    if($decode_log) {
                        foreach($decode_log as $obj) {
                            print("<p>");
                            if(!empty($obj->flags)){
                                foreach($obj->flags as $flag) {
                                    print($flag." ");
                                }
                            }
                            print($obj->seed . " = ");
                            print($obj->stagename);
                            print("</p>");
                        }
                    }
                }
                catch (Exception $e) {
                    print("The log file could not be loaded.");
                }
            ?>
        </div>
    </body>
</html>