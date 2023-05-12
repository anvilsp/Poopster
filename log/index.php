<!DOCTYPE html>
<html>
    <head>
        <title>Log | Poopster</title>
        <meta name="viewport" content="width=device-width">
    </head>
    <body>
        <div class="mainContent">
            <p><b>Disclaimer:</b> These rolls were saved by the community.</p>
            <?php
                $log_file = file_get_contents("log.txt");
                $decode_log = json_decode($log_file);
                foreach($decode_log as $obj) {
                    print("<p>!poopster ");
                    if(!empty($obj->flags)){
                        foreach($obj->flags as $flag) {
                            print($flag." ");
                        }
                    }
                    print($obj->seed . " = ");
                    print($obj->stagename);
                    print("</p>");
                }
            ?>
        </div>
    </body>
</html>