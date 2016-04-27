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
        '{DAV:}displayname',
        '{DAV:}getcontentlength',
        '{DAV:}getcontenttype'
    ), 1);
    array_shift($folders);
    foreach($folders as $key => $value){
        if(!array_key_exists('{DAV:}getcontentlength', $value)){
            doPropfind($key);
        }
        if (array_key_exists('{DAV:}getcontentlength', $value)) {
            if(strpos($value["{DAV:}getcontenttype"], "audio") !== false || strpos($value["{DAV:}getcontenttype"], "video") !== false) {
                $scriptContent[] = array(urlencode($key), urlencode($value["{DAV:}displayname"]));
                //$scriptContent .= "addToPlaylist(\"" . urlencode($key) . "\", \"" . urlencode($value["{DAV:}displayname"]) . "\");\n";
            }
        }
    }
}
doPropfind($folder);

header('Content-Type: application/json');
echo json_encode($scriptContent, true);
