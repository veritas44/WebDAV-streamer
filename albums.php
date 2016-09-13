<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 1-8-2016
 * Time: 00:31
 */

require_once("includes.php");
session_write_close();

$database = new Database();
$database->connect(DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD);

if(isset($_GET["album"])){
    $album = $_GET["album"];
    $songs = $database->get_album($album, $auth->username);
    ?>
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $album ?></h4>
    </div>
    <div class="modal-body">
        <div class="row">
        <div class="col-md-4">
            <img style="width: 100%; height: auto" src="<?php echo (empty($songs[0]["art"]) ? "img/no-image.png" : "img/album_art/" . $songs[0]["art"] . ".png"); ?>">
            <button class="btn btn-primary btn-sm" onclick="jPlaylist.remove(); addAllToPlaylist('<?php echo urlencode($album); ?>', 'album'); $('#albumModal').modal('hide'); setTimeout(function() {jPlaylist.select(0); }, 2000);">Replace playlist</button>
            <button class="btn btn-primary btn-sm" onclick="addAllToPlaylist('<?php echo urlencode($album); ?>', 'album'); $('#albumModal').modal('hide');">Add to playlist</button>
        </div>
        <div class="col-md-8">
            <table class="table table-striped table-hover tablesorter" id="albumTable">
                <thead>
                <tr>
                    <th>Artist</th>
                    <th>Track</th>
                    <th>Title</th>
                    <th>Duration</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach($songs as $song){
                    echo '<tr style="cursor:pointer;" onclick="playAudio(\'' . Sabre\HTTP\encodePath(Sabre\HTTP\encodePath($song['file'])) . '\', \'' . urlencode($song['album'] . ' - ' . $song['artist'] . ' - ' . $song['title']) . '\')"><td>' . $song['artist'] . '</td><td>' . $song['track'] . '</td><td>' . $song['title'] . '</td><td>' . gmdate('H:i:s', $song['duration']) . '</td>' .
                        '<td width="70px"><a class="btn btn-xs btn-default" href="javascript:;" onclick="event.stopPropagation(); addToPlaylist(\'' . Sabre\HTTP\encodePath(Sabre\HTTP\encodePath($song['file'])) . '\', \'' . urlencode($song['album'] . ' - ' . $song['artist'] . ' - ' . $song['title']) . '\')"><span class="glyphicon glyphicon-plus-sign"></span></a>' .
                        '<a class="btn btn-xs btn-default" href="javascript:;" onclick="event.stopPropagation(); addFavourite(\'' . urlencode(Sabre\HTTP\encodePath($song['file'])) . '\', \'' . urlencode($song['album'] . ' - ' . $song['artist'] . ' - ' . $song['title']) . '\', \'audio\')"><span class="glyphicon glyphicon-star"></span></a></td></tr>';
                }
                ?>
                </tbody>
            </table>
        </div>
        </div>
    </div>
    <script>$(document).ready(function() { $('#albumTable').tablesorter(); });</script>
    <?php
    die();
}
?>
<div class="row">
    <div class="col-xs-3">
        <h3 style="margin: 10px 0;">Albums</h3>
    </div>
    <div class="col-xs-9">
        <div class="input-group" style="margin: 10px 0;">
            <input placeholder="Search terms" class="form-control input-sm" type="search" id="albumsearch" value="<?php echo (empty($_GET["search"]) ? "" : $_GET["search"]); ?>">
            <span class="input-group-btn">
                <button class="btn btn-default btn-sm" type="button" onclick="loadPage('albums.php?search=' + $('#albumsearch').val())"><span class="glyphicon glyphicon-search"></span> Search</button>
            </span>
        </div>
    </div>
</div>
<div>
    <?php
        $albums = $database->get_library_albums($auth->username);
        $i = 0;
        foreach($albums as $album){
            if($album['album'] == ""){
                continue;
            }
            if(isset($_GET["search"]) && !empty($_GET["search"])){
                if(strpos(strtolower($album['album']), strtolower($_GET["search"])) === false){
                    continue;
                }
            }
            if($album['art'] == "") {
                $src = "img/no-image.png";
            } else {
                $src = "img/album_art/" . $album['art'] . ".png";
            }
            echo '<div class="col-lg-2 col-md-3 col-xs-4 thumb">
        <a class="thumbnail" href="javascript:;" onclick="openAlbum(\'' . urlencode($album['album']) . '\')">
        <div class="image">
            <img class="img-responsive" src="' . $src . '" title="' . $album['album'] . '">
        </div>
            <p class="album-title">' . $album['album'] . '</p>
        </a>
    </div>
    <!--div id="album' . $i . '" style="display: none;">
        
    </div-->';
            $i++;
        }
    ?>

    </div>
<div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" id="albumModal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" id="albumModalContent">

        </div>
    </div>
</div>