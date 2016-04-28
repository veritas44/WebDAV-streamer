<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 25-4-2016
 * Time: 12:44
 */

require_once("includes.php");

$folder = "";
if(isset($_GET["folder"])) {
    $folder = urldecode($_GET["folder"]);
}
$folderArray = explode('/', $folder);
$folder = str_replace(' ', '%20', $folder);

$currentURL = "";
echo '<h3>Files';
echo '<span class="float-right"><a href=\'#\' class="button" onclick="addAllToPlaylist()">Add all to playlist</a>
                <a href="#" class="button" onclick="jPlaylist.remove()">Clear playlist</a></span></h3>';
echo '<ul class="breadcrumbs">';
foreach ($folderArray as $value){
    $currentURL = $currentURL . $value . "/";
    echo '<li><a href="#" onclick="getDirectories(\'' . urlencode($currentURL) . '\')">' . $value . '</a></li>';
}
echo '</ul>';

try {
    $folders = $client->propFind($folder, array(
        '{DAV:}displayname',
        '{DAV:}getcontentlength',
        '{DAV:}getcontenttype'
    ), 1);
    array_shift($folders);

    $isContent = false;
    echo "<div id='filebrowserTable'><table>";
    foreach($folders as $key => $value){
        if(!array_key_exists('{DAV:}getcontentlength', $value)){
            $isContent = true;
            //If there is no content length, it's likely to be a folder. This loop makes sure the folders show first.
            echo "<tr><td width='25px' style='padding: 0;' class='text-center'><img src='img/icons/folder.png' alt='F'></td>
            <td><a href='#' onclick='getDirectories(\"" . urlencode($key) . "\")'>" . $value["{DAV:}displayname"] . "</a></td>
            <td align=\"right\"><a href='#' onclick='addAllToPlaylist(\"" . urlencode($key) . "\")'><img src='img/icons/add.png' alt='Add'></a></td>
            </tr>";
        }
    }
    foreach($folders as $key => $value){
        if(array_key_exists('{DAV:}getcontentlength', $value)){
            //If there is a content length, it's likely to be a file.
            if(strpos($value["{DAV:}getcontenttype"], "audio") !== false) {
                $isContent = true;
                echo "<tr><td width='25px' style='padding: 0;' class='text-center'><img src='img/icons/music.png' alt='F'></td>
            <td><a href='#' onclick='addToPlaylist(\"" . urlencode($key) . "\", \"" . urlencode($value["{DAV:}displayname"]) . "\")'>" . $value["{DAV:}displayname"] . "</a></td><td></td>
            </tr>";
            }elseif (strpos($value["{DAV:}getcontenttype"], "video") !== false){
                echo "<tr><td width='25px' style='padding: 0;' class='text-center'><img src='img/icons/film.png' alt='F'></td>
            <td><a href='#' data-open=\"video\" onclick='playVideo(\"" . urlencode($key) . "\", \"" . urlencode($value["{DAV:}displayname"]) . "\")'>" . $value["{DAV:}displayname"] . "</a></td><td></td>
            </tr>";
            }
        }
    }
    if($isContent == false){
        echo "<tr><td>It's rather empty in here</td></tr>";
    }
    echo "</table></div>";
}catch(Exception $e){
    echo $e->getMessage();
    echo '<hr>You shouldn\'t be here! <a href="#" onclick="getDirectories(\'' . urlencode($startFolder) . '\')">Go back to the root</a> or 
    <a href="#" onclick="getDirectories(\'' . $_GET["folder"] . '\')">try loading the resource again</a>';
}
