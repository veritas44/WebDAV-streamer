<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 25-4-2016
 * Time: 00:00
 */

require_once ("includes.php");

$requestURL = (($_GET["file"]));
if(filter_var(urldecode($requestURL), FILTER_VALIDATE_URL)){
    header('Location: ' . urldecode($requestURL));
    die();
}
//die("Poor you");

$md5name = md5($auth->username . $requestURL);

//if(isset($_GET["txt"])){
//    header('Location: ' . CONVERT_FOLDER_RELATIVE . "/" . $md5name . ".mp3.txt");
//    die();
//}

if(file_exists(CONVERT_FOLDER . "/" . $md5name . ".mp3") == false) {
    $response = $client->request('GET', $requestURL);

    header("HTTP/1.0 " . $response["statusCode"]);
    //header('Content-Type: audio/mpeg');
    //header('Content-Disposition: filename="'. end(explode('/', $requestURL)) . '"');
    header('Content-length: ' . $response["headers"]["content-length"][0]);
    header('Cache-Control: no-cache');
    header("Content-Transfer-Encoding: binary");

    if ($response["headers"]["content-type"][0] == "audio/mpeg") {
        //header('Content-Type: audio/mpeg');
        //echo $response["body"];
        //die();
        file_put_contents(CONVERT_FOLDER . "/" . $md5name . ".mp3", $response["body"]);
    } else {
        //header('Content-Type: audio/mpeg');
        //echo $response["body"];
        //die();
        //Generate a random name:
        file_put_contents(CONVERT_FOLDER . "/" . $md5name . "", $response["body"]);
        shell_exec(FFMPEG . " -i " . CONVERT_FOLDER . "/" . $md5name . " -threads auto -aq 3 -map_metadata 0 -id3v2_version 3 -vn " . CONVERT_FOLDER . "/" . $md5name . ".mp3");
    }
}
//$tagReader = new getID3();
//$tags = $tagReader->analyze(CONVERT_FOLDER_RELATIVE . "/" . $md5name . ".mp3");
//getid3_lib::CopyTagsToComments($tags);
//var_dump($tags);
//$tagsArray["album"] = $tags["comments"]["album"][0];
//$tagsArray["artist"] = $tags["comments"]["artist"][0];
//$tagsArray["title"] = $tags["comments"]["title"][0];
//file_put_contents(CONVERT_FOLDER_RELATIVE . "/" . $md5name . ".mp3.txt", json_encode($tagsArray));
header('Location: ' . CONVERT_FOLDER_RELATIVE . "/" . $md5name . ".mp3");
die();
//echo var_dump($response);
