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
$response = $client->request('HEAD', $requestURL);

$extension = "." . pathinfo(urldecode($requestURL), PATHINFO_EXTENSION);
$md5name = md5($auth->username . $requestURL);
$supportedMimeTypes = json_decode($_GET["support"], true);
$lockedFile = CONVERT_FOLDER . "/" . $md5name . ".lock";

//Sleep while the lock file is still present:
while (file_exists($lockedFile)){
    sleep(1);
}

file_put_contents(CONVERT_FOLDER . "/" . "progress.txt", "Loading " . urldecode($_GET["file"]) . "\r\n");

if (array_key_exists($response["headers"]["content-type"][0], $supportedMimeTypes) && $supportedMimeTypes[$response["headers"]["content-type"][0]] == true){
    header("HTTP/1.0 " . $response["statusCode"]);
    //header('Content-Type: ' . $response["headers"]["content-type"][0]);
    //header('Content-Disposition: filename="' . $md5name . $extension . '"');
    //header('Content-length: ' . $response["headers"]["content-length"][0]);
    header('Cache-Control: no-cache');
    header("Content-Transfer-Encoding: binary");
    file_put_contents(CONVERT_FOLDER . "/" . $md5name . $extension, $client->request('GET', $requestURL)["body"]);
    header('Location: ' . CONVERT_FOLDER_RELATIVE . "/" . $md5name . $extension);
    die();
} else {
    if(file_exists(CONVERT_FOLDER . "/" . $md5name . ".mp4") == false) {
        file_put_contents($lockedFile, "locked");
        $response = $client->request('GET', $requestURL);

        header("HTTP/1.0 " . $response["statusCode"]);
        if($response["statusCode"] >= 400){
            die($response["statusCode"]);
        }
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
            //Generate a random name:
            file_put_contents(CONVERT_FOLDER . "/" . $md5name . "", $response["body"]);
            shell_exec(FFMPEG . " -i " . CONVERT_FOLDER . "/" . $md5name . " -threads 0 " . CONVERT_FOLDER . "/" . $md5name . ".mp4 1>" . CONVERT_FOLDER . "/progress.txt 2>&1"); // 1>" . CONVERT_FOLDER . "/" . $md5name . ".progress 2>&1
        }
        unlink($lockedFile);
    }
    header('Location: ' . CONVERT_FOLDER_RELATIVE . "/" . $md5name . ".mp4");
    die();
//echo var_dump($response);
}




