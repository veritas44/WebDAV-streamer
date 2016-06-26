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
if(filter_var(urldecode($requestURL), FILTER_VALIDATE_URL)){
    header('Location: ' . urldecode($requestURL));
    die();
}
//die("Poor you");

$md5name = md5($auth->username . $requestURL);
$extension = "." . pathinfo(urldecode($requestURL), PATHINFO_EXTENSION);
//print_r($_GET);
$supportedMimeTypes = json_decode($_GET["support"], true);
$lockedFile = CONVERT_FOLDER . "/" . $md5name . ".lock";

//Sleep while the lock file is still present:
while (file_exists($lockedFile)){
    sleep(1);
}

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
