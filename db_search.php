<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 25-4-2016
 * Time: 23:31
 */

require_once("includes.php");
session_write_close();

$folder = "";
$search = "";

if(isset($_GET["folder"])) {
    $folder = urldecode($_GET["folder"]);
}
if(isset($_GET["search"])) {
    $search = strtolower(urldecode($_GET["search"]));
}
if(isset($_GET["timelimit"])){
    set_time_limit ($_GET["timelimit"]);
}
$folder = str_replace(' ', '%20', $folder);
$scriptContent = array();

if(!empty($search)) {
    $database = new Database();
    $database->connect(DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD);
    //var_dump($database->search_library(strtolower($dbSearch), $auth->username));
    $searchResults = null;

    $album = (isset($_GET["album"]) ? urldecode($_GET["album"]) : "");
    $artist = (isset($_GET["artist"]) ? urldecode($_GET["artist"]) : "");
    $composer = (isset($_GET["composer"]) ? urldecode($_GET["composer"]) : "");
    $genre = (isset($_GET["genre"]) ? urldecode($_GET["genre"]) : "");
    $title = (isset($_GET["title"]) ? urldecode($_GET["title"]) : "");
    $year = (isset($_GET["year"]) ? urldecode($_GET["year"]) : "");

    if(!empty($album) || !empty($artist) || !empty($composer) || !empty($genre) || !empty($title) || !empty($year)){
        $searchResults = $database->search_library_advanced($album, $artist, $composer, $genre, $title, $year, $auth->username);
    } else {
        $searchResults = $database->search_library(strtolower($search), $auth->username);
    }

    foreach($searchResults as $item){
        echo "<tr style='cursor: pointer;' onclick='playAudio(\"" . Sabre\HTTP\encodePath(Sabre\HTTP\encodePath($item['file'])) . "\", \"" . urlencode(readable_name($item['file'])) . "\")'>" .
            "<td>" . $item["artist"] . "</td>" .
            "<td>" . $item["composer"] . "</td>" .
            "<td>" . $item["album"] . "</td>" .
            "<td>" . $item["track"] . "</td>" .
            "<td>" . $item["title"] . "</td>" .
            "<td>" . gmdate('H:i:s', $item['duration']) . "</td>" .
            "<td>" . $item["genre"] . "</td>" .
            "<td>" . $item["year"] . "</td>" .
            "<td>" .
            "<a class='btn btn-xs btn-default' href='javascript:;' title='Add to playlist' onclick='event.stopPropagation(); addToPlaylist(\"" . Sabre\HTTP\encodePath(Sabre\HTTP\encodePath($item['file'])) . "\", \"" . urlencode(readable_name($item['file'])) . "\")'><span class='glyphicon glyphicon-plus-sign'></span></a> " .
            "<a class='btn btn-xs btn-default' href='javascript:;' title='Favourite' onclick='event.stopPropagation(); addFavourite(\"" . urlencode(Sabre\HTTP\encodePath($item['file'])) . "\", \"" . urlencode(readable_name(Sabre\HTTP\encodePath($item['file']))) . "\", \"audio\")'><span class='glyphicon glyphicon-star'></span></a>" .
            "</td>" .
            "</tr>";
    }
    die();
}
?>
<div class="row">
    <div class="col-xs-3">
        <h3 style="margin: 10px 0;">Search</h3>
    </div>
    <div class="col-xs-9">
        <div class="input-group" style="margin: 10px 0;">
            <input placeholder="Search terms" class="form-control input-sm" type="search" id="dbsearch" value="<?php echo $search; ?>">
            <span class="input-group-btn">
                <button class="btn btn-default btn-sm" type="button" onclick="initialDbSearch()"><span class="glyphicon glyphicon-search"></span> Search</button>
                <button class="btn btn-default btn-sm" type="button" data-toggle="collapse" data-target="#advancedSearch">Advanced search</button>
            </span>
        </div>
    </div>
</div>
<div class="row collapse" id="advancedSearch">
    <div class="well">
        <div class="form-group col-md-4">
            <div class="input-group">
                <span class="input-group-addon">Album</span>
                <input id="asearchAlbum" class="form-control" placeholder="Album" type="text">
            </div>
        </div>

        <!-- Prepended text-->
        <div class="form-group col-md-4">
            <div class="input-group">
                <span class="input-group-addon">Artist</span>
                <input id="asearchArtist" class="form-control" placeholder="Artist" type="text">
            </div>
        </div>

        <!-- Prepended text-->
        <div class="form-group col-md-4">
            <div class="input-group">
                <span class="input-group-addon">Composer</span>
                <input id="asearchComposer" class="form-control" placeholder="Composer" type="text">
            </div>
        </div>

        <!-- Select Basic -->
        <div class="form-group col-md-4">
            <div class="input-group">
                <span class="input-group-addon">Genre</span>
                <input id="asearchGenre" class="form-control" placeholder="Genre" type="text">
            </div>
        </div>
        <!-- Prepended text-->
        <div class="form-group col-md-4">
            <div class="input-group">
                <span class="input-group-addon">Title</span>
                <input id="asearchTitle" class="form-control" placeholder="Title" type="text">
            </div>
        </div>
        <div class="form-group col-md-4">
            <div class="input-group">
                <span class="input-group-addon">Year</span>
                <input id="asearchYear" class="form-control" placeholder="Year" type="text">
            </div>
        </div>
        <div class="col-md-12">
            <button class="btn btn-primary" type="button" onclick="initialDbAdvancedSearch()"><span class="glyphicon glyphicon-search"></span> Advanced search</button>
        </div>
        &nbsp;
    </div>
</div>
<div>
    <table id='databaseTable' class="table table-striped table-hover" style="width: 100%;">
        <thead>
        <tr>
            <td>Artist</td>
            <td>Composer</td>
            <td>Album</td>
            <td>Track</td>
            <td>Title</td>
            <td>Duration</td>
            <td>Genre</td>
            <td>Year</td>
            <td></td>
        </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
</div>
<script>
    var searchFolder = "<?php echo urlencode($folder); ?>";
</script>