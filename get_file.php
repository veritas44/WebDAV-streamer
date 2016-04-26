<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 25-4-2016
 * Time: 00:00
 */

require_once ("includes.php");

$requestURL = (($_GET["file"]));
//echo $requestURL;

//$requestURL = str_replace(' ', '%20', $requestURL);
$response = $client->request('GET', $requestURL);

header("HTTP/1.0 " . $response["statusCode"]);
//header('Content-Type: audio/mpeg');
//header('Content-Disposition: filename="'. end(explode('/', $requestURL)) . '"');
header('Content-length: ' . $response["headers"]["content-length"][0]);
header('Cache-Control: no-cache');
header("Content-Transfer-Encoding: binary");

if($response["headers"]["content-type"][0] == "audio/mpeg"){
    header('Content-Type: audio/mpeg');
    echo $response["body"];
} else {
    //header('Content-Type: audio/mpeg');
    //echo $response["body"];
    //die();

    //Generate a random name:
    $randomString = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 1) . substr(md5(time()), 1);
    file_put_contents(CONVERT_FOLDER . "/" . $randomString . "", $response["body"]);
    shell_exec(FFMPEG . " -i " . CONVERT_FOLDER . "/" . $randomString . " -threads auto -aq 3 -vn " . CONVERT_FOLDER . "/" . $randomString . ".mp3");
    header('Location: ' . CONVERT_FOLDER_RELATIVE . "/" . $randomString . ".mp3");
    die();
}
//echo var_dump($response);
