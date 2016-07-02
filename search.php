<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 25-4-2016
 * Time: 23:31
 */

require_once("includes.php");
session_write_close();

$folder = "";
$search = "";
if(isset($_GET["folder"])) {
    $folder = urldecode($_GET["folder"]);
}
if(isset($_GET["search"])) {
    $search = strtolower(urldecode($_GET["search"]));
}
if(isset($_GET["timelimit"])){
    set_time_limit ($_GET["timelimit"]);
}
$folder = str_replace(' ', '%20', $folder);
$scriptContent = array();


if(!empty($search)) {
    $times = 0;
    $scripts = "";
    try {
        function doPropfind($folder, $search = "")
        {
            global $client, $scriptContent, $scripts;
            $folders = $client->propFind($folder, array(
                '{DAV:}getcontenttype'
            ), 1);
            array_shift($folders);
            foreach ($folders as $key => $value) {
                if (!array_key_exists('{DAV:}getcontenttype', $value)) {
                    if (strpos(strtolower(readable_name($key)), strtolower($search)) !== false) {
                        $scriptContent[] = array(
                            "type" => "folder",
                            "file" => $key,
                            "name" => ($key)
                        );
                    }
                    //doPropfind($key, $search);
                    $scripts .= "<script>search('search.php?folder=" . $key . "&search=" . $search . "');</script>";
                }
                if (array_key_exists('{DAV:}getcontenttype', $value)) {
                    if (pathinfo(urldecode($key), PATHINFO_EXTENSION) == "pls" || pathinfo(urldecode($key), PATHINFO_EXTENSION) == "m3u" || pathinfo(urldecode($key), PATHINFO_EXTENSION) == "m3u8") {
                        if (strpos(strtolower(readable_name($key)), strtolower($search)) !== false) {
                            $scriptContent[] = array(
                                "type" => "playlist",
                                "file" => $key,
                                "name" => ($key)
                            );
                        }
                    } elseif (strpos($value["{DAV:}getcontenttype"], "audio") !== false) {
                        if (strpos(strtolower(readable_name($key)), strtolower($search)) !== false) {
                            $scriptContent[] = array(
                                "type" => "audio",
                                "file" => $key,
                                "name" => ($key)
                            );
                        }
                    } elseif (strpos($value["{DAV:}getcontenttype"], "video") !== false) {
                        if (strpos(strtolower(readable_name($key)), strtolower($search)) !== false) {
                            $scriptContent[] = array(
                                "type" => "video",
                                "file" => $key,
                                "name" => ($key)
                            );
                        }
                    }
                }
            }
        }

        doPropfind($folder, $search);
    } catch (Exception $e) {
        $times++;
        if($times > 3) {
            doPropfind($folder, $search);
        }
    }
    foreach($scriptContent as $item){
        if($item["type"] == "folder") {
            echo "<tr><td width='25px' class='table-icon'><span class='glyphicon glyphicon-folder-open'></span> </td>
            <td><a href='#' onclick='getDirectories(\"" . urlencode($item["file"]) . "\")'>" . readable_name($item["name"]) . "</a></td>
            <td>" . dirname(urldecode($item["file"])) . "</td>
            <td width='75px' class='table-icon' align=\"right\"><a class='btn btn-xs btn-default' href='javascript:;' onclick='addAllToPlaylist(\"" . urlencode($item["file"]) . "\")' title='Add all files to the playlist'><span class='glyphicon glyphicon-plus-sign'></span></td>
            </tr>";
        }
    }

    foreach($scriptContent as $item){
        if($item["type"] == "playlist") {
            echo "<tr><td width='25px' class='table-icon'><span class='glyphicon glyphicon-list'></span></td>
            <td><a href='javascript:;' data-toggle=\"modal\" data-target=\"#replacePlaylist\" onclick='setPlaylist(\"" . Sabre\HTTP\encodePath($item["file"]) . "\", \"" . urlencode(readable_name($item["name"])) . "\")'>" . readable_name($item["name"]) . "</a></td>
            <td>" . dirname(urldecode($item["file"])) . "</td>
            <td width='75px' class='table-icon' align=\"right\">
                <a class='btn btn-xs btn-default' href='javascript:;' onclick='removeFile(\"" . urlencode($item["file"]) . "\")' title='Remove this playlist'><span class='glyphicon glyphicon-remove'></span></a> 
                <a class='btn btn-xs btn-default' href='javascript:;' onclick='addFavourite(\"" . urlencode($item["file"]) . "\", \"" . urlencode(readable_name($item["name"])) . "\", \"playlist\")' title='Favourite this playlist'><span class='glyphicon glyphicon-star'></span></a>
            </td>
            </tr>";
        }
    }

    foreach($scriptContent as $item){
        if($item["type"] == "audio") {
            echo "<tr><td width='25px' class='table-icon'><span class='glyphicon glyphicon-music'></span></td>
            <td><a href='javascript:;' onclick='addToPlaylist(\"" . Sabre\HTTP\encodePath($item["file"]) . "\", \"" . urlencode(readable_name($item["name"])) . "\")'>" . readable_name($item["name"]) . "</a></td>
            <td>" . dirname(urldecode($item["file"])) . "</td>
            <td width='75px' class='table-icon' align=\"right\">
                <a class='btn btn-xs btn-default' href='javascript:;' onclick='addFavourite(\"" . urlencode($item["file"]) . "\", \"" . urlencode(readable_name($item["name"])) . "\", \"audio\")' title='Favourite this audio'><span class='glyphicon glyphicon-star'></span></a>
            </td>
            </tr>";
        }
    }

    foreach($scriptContent as $item){
        if($item["type"] == "video") {
            echo "<tr><td width='25px' class='table-icon'><span class='glyphicon glyphicon-film'></span></td>
            <td><a href='javascript:;' onclick='playVideo(\"" . Sabre\HTTP\encodePath($item["file"]) . "\", \"" . urlencode(readable_name($item["name"])) . "\")'>" . readable_name($item["file"]) . "</a></td>
            <td>" . dirname(urldecode($item["file"])) . "</td>
            <td width='75px' class='table-icon' align=\"right\">
                <a class='btn btn-xs btn-default' href='javascript:;' onclick='addFavourite(\"" . urlencode($item["file"]) . "\", \"" . urlencode(readable_name($item["name"])) . "\", \"video\")' title='Favourite this video'><span class='glyphicon glyphicon-star'></span></a>
            </td>
            </tr>";
        }
    }

    echo $scripts;

    die();
}
?>
<div class="row">
    <div class="col-xs-3">
        <h3 style="margin: 10px 0;">Search</h3>
    </div>
    <div class="col-xs-9">
        <div class="input-group" style="margin: 10px 0;">
            <input placeholder="Search terms (the filename or part of it)" class="form-control input-sm" type="search" id="filesearch" value="<?php echo $search; ?>">
            <span class="input-group-btn">
                <button class="btn btn-default btn-sm" type="button" onclick="initialSearch(searchFolder)"><span class="glyphicon glyphicon-search"></span> Search</button>
            </span>
        </div>
    </div>
</div>

<div>
    <table id='searchTable' class="table table-striped table-hover" style="width: 100%;">
        <tbody>

        </tbody>
    </table>
    <div class="loader" id="searchLoader" style="display: none;"></div>
</div>
<script>
    var searchFolder = "<?php echo urlencode($folder); ?>";
</script>