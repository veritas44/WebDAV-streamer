<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 7-8-2016
 * Time: 22:40
 */
require_once("includes.php");
session_write_close();


if(isset($_GET["playlist"])){
    echo file_get_contents("https://www.youtube.com/list_ajax?action_get_list=1&style=json&list=" . $_GET["playlist"]);
    die();
}
if(isset($_GET["video"])){
    //Old method, still works, but not for VEVO et al, but now that we have youtube-dl, let's just use that...
    //$videoArray = array();
    //parse_str(file_get_contents("https://youtube.com/get_video_info?video_id=" . $_GET["video"]), $videoArray); //Turn video info into array
    //print_r($videoArray);
    //echo $videoArray["title"];

    echo shell_exec(YOUTUBE_DL . " -e https://www.youtube.com/watch?v=" . escapeshellcmd($_GET["video"]));
    die();
}
?>

<div class="row">
    <div class="col-xs-12">
        <h3 style="margin: 10px 0;">YouTube</h3>
    </div>
</div>
<div class="row col-xs-12">
    <p>If you want, you can add a YouTube video to your playlist. You can even add a whole YouTube playlist to your playlist. Please note that this feature is very much still in beta and might not work as expected.</p>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label for="youtubeURL">YouTube URL: </label>
        <input type="text" class="form-control" id="youtubeURL" style="width: 100%">
    </div>
    <button onclick='addYouTube(youtube_parser($("#youtubeURL").val()), "play"); $("#youtubeURL").val("");' class="btn btn-primary">Play audio</button>
    <button onclick='addYouTube(youtube_parser($("#youtubeURL").val()), "add"); $("#youtubeURL").val("");' class="btn btn-primary">Add to playlist</button>
    <br><br>
</div>
<!--small>This service is provided by <a href="https://youtube2mp3.cc" target="_blank">YouTube2MP3.cc</a>. </small-->
<div class="col-md-6">
    <div class="form-group">
        <label for="youtubePlaylistURL">YouTube playlist URL: </label>
        <input title="text" class="form-control" id="youtubePlaylistURL" style="width: 100%">
    </div>
    <button onclick='addYouTubePlaylist(youtube_playlist_parser($("#youtubePlaylistURL").val())); $("#youtubePlaylistURL").val("");' class="btn btn-primary">Add to playlist</button>
    <button onclick='jPlaylist.remove(); addYouTubePlaylist(youtube_playlist_parser($("#youtubePlaylistURL").val())); $("#youtubePlaylistURL").val("");' class="btn btn-primary">Replace playlist</button>
</div>
<div class="row col-xs-12" id="youtubeProgress"></div>