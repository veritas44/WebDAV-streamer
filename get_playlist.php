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
//header('Content-length: ' . $response["headers"]["content-length"][0]);
header('Cache-Control: no-cache');
header("Content-Transfer-Encoding: binary");

if (pathinfo(urldecode($requestURL), PATHINFO_EXTENSION) == "pls") {
    $filename = CONVERT_FOLDER . "/" . $md5name . ".pls";
    file_put_contents($filename, $response["body"]);
    $playlist = new Playlist($filename, $requestURL);
    header('Content-Type: application/json');
    echo json_encode($playlist->openPLS());
} elseif (pathinfo(urldecode($requestURL), PATHINFO_EXTENSION) == "m3u" || pathinfo(urldecode($requestURL), PATHINFO_EXTENSION) == "m3u8") {
    $filename = CONVERT_FOLDER . "/" . $md5name . ".m3u";
    file_put_contents($filename, $response["body"]);
    $playlist = new Playlist($filename, $requestURL);
    header('Content-Type: application/json');
    echo json_encode($playlist->openM3U());
} else {
    echo "Extension invalid (" . pathinfo(urldecode($requestURL), PATHINFO_EXTENSION) . ")";
}