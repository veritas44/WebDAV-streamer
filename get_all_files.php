<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 25-4-2016
 * Time: 23:31
 */

require_once("includes.php");

$scriptContent = array();

function doPropfind($folder){
    global $client, $scriptContent;
    $folders = $client->propFind($folder, array(
        '{DAV:}getcontenttype'
    ), 1);
    array_shift($folders);
    foreach($folders as $key => $value){
        if(!array_key_exists('{DAV:}getcontenttype', $value)){
            doPropfind($key);
        }
        if (array_key_exists('{DAV:}getcontenttype', $value)) {
            if(strpos($value["{DAV:}getcontenttype"], "audio") !== false) {
                if(pathinfo(urldecode($key), PATHINFO_EXTENSION) != "pls" && pathinfo(urldecode($key), PATHINFO_EXTENSION) != "m3u" && pathinfo(urldecode($key), PATHINFO_EXTENSION) != "m3u8") {
                    $scriptContent[] = array(urlencode($key), urlencode(readable_name($key)));
                    //$scriptContent .= "addToPlaylist(\"" . urlencode($key) . "\", \"" . urlencode($value["{DAV:}displayname"]) . "\");\n";
                }
            }
        }
    }
}

if(isset($_GET["folder"])) {
    $folder = urldecode($_GET["folder"]);
    $folder = "";
    $folder = str_replace(' ', '%20', $folder);

    doPropfind($folder);
} elseif (isset($_GET["album"])){
    $database = new Database();
    $database->connect(DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD);

    foreach($database->get_album($_GET["album"], $auth->username) as $item){
        $scriptContent[] = array(urlencode(Sabre\HTTP\encodePath($item['file'])), urlencode(readable_name($item['file'])));
    }
} elseif (isset($_GET["artist"])) {
    $database = new Database();
    $database->connect(DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD);

    foreach($database->get_artist($_GET["artist"], $auth->username) as $item){
        $scriptContent[] = array(urlencode(Sabre\HTTP\encodePath($item['file'])), urlencode(readable_name($item['file'])));
    }
} elseif (isset($_GET["genre"])) {
    $database = new Database();
    $database->connect(DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD);

    foreach($database->get_genre($_GET["genre"], $auth->username) as $item){
        $scriptContent[] = array(urlencode(Sabre\HTTP\encodePath($item['file'])), urlencode(readable_name($item['file'])));
    }
}
header('Content-Type: application/json');
echo json_encode($scriptContent, true);
