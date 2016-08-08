<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 7-8-2016
 * Time: 22:40
 */
require_once("includes.php");
session_write_close();
?>

<div class="row">
    <div class="col-xs-12">
        <h3 style="margin: 10px 0;">YouTube</h3>
    </div>
</div>
<div class="row col-xs-12">
    <p>Please note that this feature is very much still in beta and might not work as expected.</p>
    <div class="form-group">
        <label for="youtubeURL">YouTube URL: </label>
        <input type="text" class="form-control" id="youtubeURL" style="width: 100%">
    </div>
    <button onclick='addYouTube(youtube_parser($("#youtubeURL").val())); $("#youtubeURL").val("");' class="btn btn-primary">Add to playlist</button>
    <small class="text-right">This service is provided by <a href="https://youtubeinmp3.com" target="_blank">YouTubeInMP3.com</a>. Use at your own risk.</small>
</div>
<iframe id="youtubeIframe" style="display: none;"></iframe>