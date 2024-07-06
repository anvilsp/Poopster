<!DOCTYPE html>
<html>
    <head>
        <title>Log | Poopster</title>
        <link rel="stylesheet" href="/style.css">
        <meta name="viewport" content="width=device-width">
    </head>
    
    <body>
        <div class="footer">
            <a href="..">Back</a> | <a href="last">Previous Random Rolls</a>
            <br><br>
            <?php
                if(!isset($_GET['sort']) || $_GET['sort'] != "desc")
                    echo '<a href="?sort=desc">Sort by Descending</a>';
                else
                    echo '<a href="?sort=asc">Sort by Ascending</a>';
            ?>
        </div>
        <?php
            try {
                $db = new SQLite3('poopster.sqlite', SQLITE3_OPEN_READWRITE);
                if(!isset($_GET['sort']) || $_GET['sort'] != "desc") {
                    $entry_query = $db->query('SELECT * FROM saved ORDER BY id');
                }else{
                    $entry_query = $db->query('SELECT * FROM saved ORDER BY id DESC');
                }
                while($row = $entry_query->fetchArray(SQLITE3_ASSOC)) {
                    print("<div class='savedBlock'>");
                    $flags = json_decode(html_entity_decode($row['flags']), true);
                    print("<h2>".$row['stagename']."</h2>");
                    print("!poopster ");
                    foreach($flags as $flag) {
                        print($flag ." ");
                    }
                    print($row['seed']);
                    print("<br><small>Log ID #".$row['id']."</small>");
                    print("</div>");
                    print("<br>");
                }
            } catch (Exception $e) {
                echo("Database file could not be loaded.");
            }
        ?>
        <div class="footer">
            <small>
                Disclaimer: These stages are logged by the community.
                <br>
                Please message me on Discord (anvilsp) if something displayed on this page should be removed. 
            </small>
        </div>
    </body>
</html>