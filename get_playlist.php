<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 3-5-2016
 * Time: 21:43
 */

require_once ("includes.php");

$requestURL = (($_GET["file"]));
$md5name = md5($auth->username . $requestURL);
$response = $client->request('GET', $requestURL);

header("HTTP/1.0 " . $response["statusCode"]);
//header('Content-Type: audio/mpeg');
//header('Content-Disposition: filename="'. end(explode('/', $requestURL)) . '"');
header('Content-length: ' . $response["headers"]["content-length"][0]);
header('Cache-Control: no-cache');
header("Content-Transfer-Encoding: binary");

if ($response["headers"]["content-type"][0] == "audio/x-scpls") {
    $filename = CONVERT_FOLDER . "/" . $md5name . ".pls";
    file_put_contents($filename, $response["body"]);
    $playlist = new Playlist($filename, $requestURL);
    header('Content-Type: application/json');
    echo json_encode($playlist->openPLS());
} 
if ($response["headers"]["content-type"][0] == "audio/x-mpegurl") {
    $filename = CONVERT_FOLDER . "/" . $md5name . ".m3u";
    file_put_contents($filename, $response["body"]);
    $playlist = new Playlist($filename, $requestURL);
    header('Content-Type: application/json');
    echo json_encode($playlist->openM3U());
}