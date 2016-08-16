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

if(isset($_GET["genre"])){
    $genre = $_GET["genre"];
    $songs = $database->get_genre($genre, $auth->username);
    ?>
    <button class="btn btn-primary btn-sm" onclick="jPlaylist.remove(); addAllToPlaylist('<?php echo urlencode($genre); ?>', 'genre'); setTimeout(function() {jPlaylist.select(0); }, 2000);">Replace playlist</button>
    <button class="btn btn-primary btn-sm" onclick="addAllToPlaylist('<?php echo urlencode($genre); ?>', 'genre');">Add to playlist</button>
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <td>Artist</td>
            <td>Album</td>
            <td>Track</td>
            <td>Title</td>
            <td>Duration</td>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($songs as $song){
            echo '<tr style="cursor:pointer;" onclick="playAudio(\'' . Sabre\HTTP\encodePath(Sabre\HTTP\encodePath($song['file'])) . '\', \'' . urlencode($song['album'] . ' - ' . $song['artist'] . ' - ' . $song['title']) . '\')"><td>' . $song['artist'] . '</td><td>' . $song['album'] . '</td><td>' . $song['track'] . '</td><td>' . $song['title'] . '</td><td>' . gmdate('H:i:s', $song['duration']) . '</td>' .
        '<td><a class="btn btn-xs btn-default" href="javascript:;" onclick="event.stopPropagation(); addToPlaylist(\'' . Sabre\HTTP\encodePath(Sabre\HTTP\encodePath($song['file'])) . '\', \'' . urlencode($song['album'] . ' - ' . $song['artist'] . ' - ' . $song['title']) . '\')"><span class="glyphicon glyphicon-plus-sign"></span></a> ' .
                '<a class="btn btn-xs btn-default" href="javascript:;" onclick="event.stopPropagation(); addFavourite(\'' . urlencode(Sabre\HTTP\encodePath($song['file'])) . '\', \'' . urlencode($song['album'] . ' - ' . $song['artist'] . ' - ' . $song['title']) . '\', \'audio\')"><span class="glyphicon glyphicon-star"></span></a></td></tr>';
        }
        ?>
        </tbody>
    </table>
    <?php
    die();
}
?>
<div class="row">
    <div class="col-xs-3">
        <h3 style="margin: 10px 0;">Genres</h3>
    </div>
    <div class="col-xs-9">
        <div class="input-group" style="margin: 10px 0;">
            <input placeholder="Search terms" class="form-control input-sm" type="search" id="genresearch" value="<?php echo (empty($_GET["search"]) ? "" : $_GET["search"]); ?>">
            <span class="input-group-btn">
                <button class="btn btn-default btn-sm" type="button" onclick="loadPage('genres.php?search=' + $('#artistsearch').val())"><span class="glyphicon glyphicon-search"></span> Search</button>
            </span>
        </div>
    </div>
</div>
<div>
    <div class="row">
        <div class="col-md-7 col-md-push-5" id="genreSongList">

        </div>
        <div class="col-md-5 col-md-pull-7">
            <table class="table table-striped table-hover">
            <?php
            $genres = $database->get_library_genres($auth->username);
            //var_dump($genres);
            foreach ($genres as $genre){
                if(empty($genre["genre"])){
                    continue;
                }
                if(isset($_GET["search"]) && !empty($_GET["search"])){
                    if(strpos(strtolower($genre['genre']), strtolower($_GET["search"])) === false){
                        continue;
                    }
                }

                echo "<tr><td><a href='javascript:;' onclick='openGenre(\"" . urlencode($genre["genre"]) . "\")'>" . $genre["genre"] . "</a></td></tr>";
            }
            ?>
                </table>
        </div>
</div>