<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 21-5-2016
 * Time: 14:30
 */
require_once ("includes.php");
$database = new Database();
$database->connect(DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD);
if(isset($_GET["action"])) {
    if ($_GET["action"] == "open") {
        try {
            header('Content-Type: application/json');
            $favourites = $database->get_data($username)["favourites"]["favourites"];
            if (empty($favourites)) {
                echo "[]";
            } else {
                echo $favourites;
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        die();
    } elseif ($_GET["action"] == "update") {
        try {
            $database->update_favourites($username, file_get_contents('php://input'));
            //file_put_contents(FAVOURITES_FOLDER . $fileFriendlyUsername, file_get_contents('php://input'));
            echo "success";
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        die();
    } else {

    }
}
?>
<div class="row">
    <div>
        <h3 style="margin: 10px;">Favourites</h3>
    </div>
</div>
<div>
    <table class="table table-striped table-hover" id="favouriteTable">
        <tbody>
        <?php
        $favourites = json_decode($database->get_data($username)["favourites"]["favourites"], true);
        if (is_array($favourites) || is_object($favourites)) {
            foreach ($favourites as $favourite) {
                echo "<tr><td class='sorter' width='25px'></td>" .
                    "<td><a href='javascript:;' onclick='openFavourite(\"" . $favourite["file"] . "\", \"" . urlencode($favourite["name"]) . "\", \"" . $favourite["type"] . "\")'>" . $favourite["name"] . "</a></td>" .
                    "<td align='right' width='75px'><a href='javascript:;' onclick='removeFavourite($(this).closest(\"tr\").index())' title='Remove from favourites' class='btn btn-xs btn-default'><span class='glyphicon glyphicon-remove'></span></a> </td></tr>";
            }
        } else {
            echo "<tr>It's rather empty in here. Add a favourite by clicking on the star next to a file</tr>";
        }
        ?>
        </tbody>
    </table>
</div>
<script>
    var dataTable;
    $(document).ready(function() {
        /*
        var dataTable;
        dataTable = $('#favouriteTable').DataTable({
            "paging":   false,
            "ordering": false,
            "bInfo" : false
        });
        */
        $.rowSorter.destroy('#favouriteTable');
        $('#favouriteTable').rowSorter({
            handler: 'td.sorter',
            onDragStart: function(tbody, row, ind,ex)
            {
                //log('index: ' + index);
                //console.log('onDragStart: active row\'s index is ' + index);
            },
            onDrop: function(tbody, row, new_index, old_index)
            {
                //log('old_index: ' + old_index + ', new_index: ' + new_index);
                //console.log('onDrop: row moved from ' + old_index + ' to ' + new_index);
                var favouriteFiles = JSON.parse(getFavourites());
                favouriteFiles.move(old_index, new_index);
                saveFavourites(favouriteFiles);
            }
        });
    });
    /*
    $("#searchbox").on("keyup search input paste cut", function() {
        //console.log(this.value);
        dataTable.search(this.value).draw();
    });
    */
</script>
