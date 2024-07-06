<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>

    <body>
        <?php
            header('Content-type: text/html; charset=utf-8');
            try {
                $db = new SQLite3('../../poopster.sqlite', SQLITE3_OPEN_READWRITE);
                $entry_query = $db->query('SELECT * FROM saved');
                while($row = $entry_query->fetchArray(SQLITE3_ASSOC)) {
                    print("#".$row['id']." | ");
                    $flags = json_decode(html_entity_decode($row['flags']), true);
                    print("!poopster ");
                    foreach($flags as $flag) {
                        print($flag ." ");
                    }
                    print($row['seed'] . " = " . $row['stagename']);
                    print("<br>");
                }
            } catch (Exception $e) {
                echo("Database file could not be loaded.");
            }
        ?>
    </body>
</html>