<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 1-8-2016
 * Time: 13:27
 */

require_once("includes.php");
session_write_close();

$database = new Database();
$database->connect(DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD);

if(isset($_GET["artist"])){
    $artist = $_GET["artist"];
    $songs = $database->get_artist($artist, $auth->username);
    ?>
    <button class="btn btn-primary btn-sm" onclick="jPlaylist.remove(); addAllToPlaylist('<?php echo urlencode($artist); ?>', 'artist'); setTimeout(function() {jPlaylist.select(0); }, 2000);">Replace playlist</button>
    <button class="btn btn-primary btn-sm" onclick="addAllToPlaylist('<?php echo urlencode($artist); ?>', 'artist');">Add to playlist</button>
    <table class="table table-striped table-hover tablesorter" id="artistTable">
        <thead>
        <tr>
            <th>Album</th>
            <th>Track</th>
            <th>Title</th>
            <th>Duration</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($songs as $song){
            echo '<tr style="cursor:pointer;" onclick="playAudio(\'' . Sabre\HTTP\encodePath(Sabre\HTTP\encodePath($song['file'])) . '\', \'' . urlencode($song['album'] . ' - ' . $song['artist'] . ' - ' . $song['title']) . '\')"><td>' . $song['album'] . '</td><td>' . $song['track'] . '</td><td>' . $song['title'] . '</td><td>' . gmdate('H:i:s', $song['duration']) . '</td>' .
                '<td><a class="btn btn-xs btn-default" href="javascript:;" onclick="event.stopPropagation(); addToPlaylist(\'' . Sabre\HTTP\encodePath(Sabre\HTTP\encodePath($song['file'])) . '\', \'' . urlencode($song['album'] . ' - ' . $song['artist'] . ' - ' . $song['title']) . '\')"><span class="glyphicon glyphicon-plus-sign"></span></a> ' .
                '<a class="btn btn-xs btn-default" href="javascript:;" onclick="event.stopPropagation(); addFavourite(\'' . urlencode(Sabre\HTTP\encodePath($song['file'])) . '\', \'' . urlencode($song['album'] . ' - ' . $song['artist'] . ' - ' . $song['title']) . '\', \'audio\')"><span class="glyphicon glyphicon-star"></span></a></td></tr>';
        }
        ?>
        </tbody>
    </table>
    <script>$(document).ready(function() { $('#artistTable').tablesorter(); });</script>
    <?php
    die();
}
?>
<div class="row">
    <div class="col-xs-3">
        <h3 style="margin: 10px 0;">Artists</h3>
    </div>
    <div class="col-xs-9">
        <div class="input-group" style="margin: 10px 0;">
            <input placeholder="Search terms" class="form-control input-sm" type="search" id="artistsearch" value="<?php echo (empty($_GET["search"]) ? "" : $_GET["search"]); ?>">
            <span class="input-group-btn">
                <button class="btn btn-default btn-sm" type="button" onclick="loadPage('artists.php?search=' + $('#artistsearch').val())"><span class="glyphicon glyphicon-search"></span> Search</button>
            </span>
        </div>
    </div>
</div>
<div>
    <div class="row">
        <div class="col-md-7 col-md-push-5" id="artistSongList">

        </div>
        <div class="col-md-5 col-md-pull-7">
            <table class="table table-striped table-hover">
            <?php
            $artists = $database->get_library_artists($auth->username);
            foreach ($artists as $artist){
                if(empty($artist["artist"])){
                    continue;
                }
                if(isset($_GET["search"]) && !empty($_GET["search"])){
                    if(strpos(strtolower($artist['artist']), strtolower($_GET["search"])) === false){
                        continue;
                    }
                }

                echo "<tr><td><a href='javascript:;' onclick='openArtist(\"" . urlencode($artist["artist"]) . "\")'>" . $artist["artist"] . "</a></td></tr>";
            }
            ?>
                </table>
        </div>
</div>