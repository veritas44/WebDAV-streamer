<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 25-4-2016
 * Time: 23:31
 */

require_once("includes.php");

$folder = "";
if(isset($_GET["folder"])) {
    $folder = urldecode($_GET["folder"]);
}
$folder = str_replace(' ', '%20', $folder);
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
doPropfind($folder);

header('Content-Type: application/json');
echo json_encode($scriptContent, true);
