<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 3-5-2016
 * Time: 23:02
 */

require_once ("includes.php");

$file = $_POST["file"];
$jsonPlaylist = $_POST["playlist"];

$playlist = new Playlist($file, $file);
//echo $playlist->savePLS($jsonPlaylist);