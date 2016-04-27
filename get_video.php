<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 25-4-2016
 * Time: 00:00
 */

require_once ("includes.php");

$requestURL = (($_GET["file"]));
$md5name = md5($requestURL);
if(file_exists(CONVERT_FOLDER . "/" . $md5name . ".mp4") == false) {
    $response = $client->request('GET', $requestURL);

    header("HTTP/1.0 " . $response["statusCode"]);
    //header('Content-Type: audio/mpeg');
    //header('Content-Disposition: filename="'. end(explode('/', $requestURL)) . '"');
    header('Content-length: ' . $response["headers"]["content-length"][0]);
    header('Cache-Control: no-cache');
    header("Content-Transfer-Encoding: binary");


    if ($response["headers"]["content-type"][0] == "video/mp4") {
        //header('Content-Type: audio/mpeg');
        //echo $response["body"];
        //die();
        file_put_contents(CONVERT_FOLDER . "/" . $md5name . ".mp4", $response["body"]);
    } else {
        //header('Content-Type: audio/mpeg');
        //echo $response["body"];
        //die();
        //Generate a random name:
        file_put_contents(CONVERT_FOLDER . "/" . $md5name . "", $response["body"]);
        shell_exec(FFMPEG . " -i " . CONVERT_FOLDER . "/" . $md5name . " -threads auto " . CONVERT_FOLDER . "/" . $md5name . ".mp4");
    }
}
header('Location: ' . CONVERT_FOLDER_RELATIVE . "/" . $md5name . ".mp4");
die();
//echo var_dump($response);
