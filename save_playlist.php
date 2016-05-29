<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 3-5-2016
 * Time: 23:02
 */

require_once ("includes.php");

$file = urldecode(Sabre\HTTP\decodePath($_POST["file"]));
echo $file;
$jsonPlaylist = urldecode($_POST["playlist"]);
//echo $jsonPlaylist;

$playlist = new Playlist($file, $file);
if($_POST["type"] == "m3u"){
    //echo $jsonPlaylist;
    $response = $playlist->saveM3U($jsonPlaylist);
} else {
    $response = $playlist->savePLS($jsonPlaylist);
}
if($response["statusCode"] < 400) {
    echo "Succesfully created the playlist";
} else {
    echo "Something went wrong\n\nDetails:";
    echo $file . "\n";
    echo $jsonPlaylist . "\n";
    print_r($response);
}