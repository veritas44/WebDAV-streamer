<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 25-4-2016
 * Time: 00:00
 */

require_once ("includes.php");

session_write_close();
$requestURL = ($_GET["file"]);

//die($requestURL);
$md5name = md5($auth->username . $requestURL);
$extension = "." . pathinfo(urldecode($requestURL), PATHINFO_EXTENSION);

$supportedMimeTypes = json_decode($_GET["support"], true);
$lockedFile = CONVERT_FOLDER . "/" . $md5name . ".lock";

//Sleep while the lock file is still present:
while (file_exists($lockedFile)){
    sleep(1);
}

if(substr(urldecode($requestURL), 0, 4) === "#yt_"){
    $extension = ".mp4";
    if(file_exists(CONVERT_FOLDER . "/" . $md5name . $extension) == false) {
        file_put_contents($lockedFile, "locked");
        //echo YOUTUBE_DL . " -f 'bestvideo[ext=mp4]+bestaudio[ext=m4a]/bestvideo+bestaudio' --merge-output-format mp4 -o '" . CONVERT_FOLDER . "/" . $md5name . $extension . "' https://www.youtube.com/watch?v=" . escapeshellcmd(str_replace("#yt_", "", urldecode($requestURL)));
        //die();
        $video = shell_exec(YOUTUBE_DL . " -f 'bestvideo[ext=mp4]+bestaudio[ext=m4a]/bestvideo+bestaudio' --merge-output-format mp4 -o '" . CONVERT_FOLDER . "/" . $md5name . $extension . "' https://www.youtube.com/watch?v=" . escapeshellcmd(str_replace("#yt_", "", urldecode($requestURL))));
        //file_put_contents(CONVERT_FOLDER . "/" . $md5name . $extension, $video);
        unlink($lockedFile);
    }
    /*
    //echo YOUTUBE_DL . " -g \"https://www.youtube.com/watch?v=" . escapeshellcmd(substr(urldecode($requestURL), 4)) . "\"";
    //header('Content-Type: video/mp4');

    die($videoURL);
    //echo file_get_contents($videoURL);
    //error_reporting(E_ALL); ini_set('display_errors', 'On');
    $videoURL = str_replace("\r", "", $videoURL);
    $videoURL = str_replace("\n", "", $videoURL);
    //header("Access-Control-Allow-Origin: *");
    //readfile($videoURL);
    //header('Location: ' . ($videoURL));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $videoURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $out = curl_exec($ch);
    curl_close($ch);


    header('Content-type: video/mp4');
    header('Content-type: video/mpeg');
    header('Content-disposition: inline');
    header("Content-Transfer-Encoding:­ binary");
    header("Content-Length: ".filesize($out));
    echo $out;
    exit();
    */
}

if(filter_var(urldecode($requestURL), FILTER_VALIDATE_URL)){
    header('Location: ' . urldecode($requestURL));
    die();
}
//die("Poor you");

//print_r($_GET);


if(file_exists(CONVERT_FOLDER . "/" . $md5name . $extension) == false) {
    if(file_exists(CONVERT_FOLDER . "/" . $md5name . ".mp3") == false) {
        file_put_contents($lockedFile, "locked");
        $response = $client->request('GET', $requestURL);
        if ($response["statusCode"] >= 400) {
            $response = $client->request('GET', Sabre\HTTP\decodePath($requestURL));
            //echo print_r($response);
            //die();
        }
        header("HTTP/1.0 " . $response["statusCode"]);
        //header('Content-Type: audio/mpeg');
        //header('Content-Disposition: filename="'. end(explode('/', $requestURL)) . '"');
        header('Content-length: ' . $response["headers"]["content-length"][0]);
        //header('Cache-Control: no-cache');
        header("Content-Transfer-Encoding: binary");

        //var_dump(array_key_exists($response["headers"]["content-type"][0], $supportedMimeTypes));
        //echo $supportedMimeTypes[$response["headers"]["content-type"][0]];
        //die();
        if (array_key_exists($response["headers"]["content-type"][0], $supportedMimeTypes) && $supportedMimeTypes[$response["headers"]["content-type"][0]] == true) {
            file_put_contents(CONVERT_FOLDER . "/" . $md5name . $extension, $response["body"]);
        } else {
            //die(print_r($response));
            $extension = ".mp3";
            file_put_contents(CONVERT_FOLDER . "/" . $md5name, $response["body"]);
            shell_exec(FFMPEG . " -i " . CONVERT_FOLDER . "/" . $md5name . " -threads 0 -map_metadata 0 -id3v2_version 3 -vn " . CONVERT_FOLDER . "/" . $md5name . ".mp3");
        }
        unlink($lockedFile);
    } else {
        $extension = ".mp3";
    }
}

header('Location: ' . CONVERT_FOLDER_RELATIVE . "/" . $md5name . $extension);
die();
