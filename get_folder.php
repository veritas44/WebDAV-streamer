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
    //$folder = urldecode($_GET["folder"]);
    $folder = $_GET["folder"];
}
$folder = utf8_encode($folder);
$folderArray = explode('/', urldecode($folder));
$folder = str_replace(' ', '%20', $folder);
//$folder = sabre_urlencode($folder);
$currentURL = "";
?>
    <div class="row">
        <div class="col-md-3">
            <h3>Files</h3>
        </div>
        <div class="col-md-9 text-right form-inline" style="margin-top: 20px; margin-bottom: 10px;">
            <input aria-controls="filebrowserTable" placeholder="Search" class="form-control input-sm" type="search" id="searchbox">
            <a href="javascript:;" class="btn blue" onclick="addAllToPlaylist()">Add all to playlist</a>
        </div>
    </div>

    <div style="height: 5px;"></div>
    <div class="row">
        <ol class="breadcrumb">
            <?php
            //echo $folder;
            //echo $_GET["folder"];
            foreach ($folderArray as $value){
                $currentURL = $currentURL . $value . "/";
                echo '<li><a href="#" onclick="getDirectories(\'' . urlencode(Sabre\HTTP\encodePath($currentURL)) . '\')">' . $value . '</a></li>';
            }
            ?>
        </ol>
    </div>
<?php

try {
    $folders = $client->propFind($folder, array(
        '{DAV:}getcontenttype'
    ), 1);

    //print_r($folders);
    //var_dump($folders);
    array_shift($folders);

    $isContent = false;
    ?>
    <div>
        <table id='filebrowserTable' class="table table-striped table-hover" style="width: 100%;">
            <thead style='display: none'>
            <tr>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
            </tr>
            </thead>
            <tbody>

            <?php
            foreach($folders as $key => $value){
                //echo $key;
                if(!array_key_exists('{DAV:}getcontenttype', $value)){
                    $isContent = true;
                    //If there is no content length, it's likely to be a folder. This loop makes sure the folders show first.
                    echo "<tr><td width='25px' class='table-icon'><span class='glyphicon glyphicon-folder-open'></span> </td>
            <td><a href='#' onclick='getDirectories(\"" . urlencode($key) . "\")'>" . readable_name($key) . "</a></td>
            <td width='75px' class='table-icon' align=\"right\"><a class='btn btn-xs btn-default' href='javascript:;' onclick='addAllToPlaylist(\"" . urlencode($key) . "\")' title='Add all files to the playlist'><span class='glyphicon glyphicon-plus-sign'></span></td>
            </tr>";
                }
            }
            foreach($folders as $key => $value){
                if(array_key_exists('{DAV:}getcontenttype', $value)){
                    //If there is a content type, it's likely to be a file.
                    if(pathinfo(urldecode($key), PATHINFO_EXTENSION) == "pls" || pathinfo(urldecode($key), PATHINFO_EXTENSION) == "m3u" || pathinfo(urldecode($key), PATHINFO_EXTENSION) == "m3u8"){
                        $isContent = true;
                        echo "<tr><td width='25px' class='table-icon'><span class='glyphicon glyphicon-list'></span></td>
            <td><a href='javascript:;' data-toggle=\"modal\" data-target=\"#replacePlaylist\" onclick='setPlaylist(\"" . Sabre\HTTP\encodePath($key) . "\", \"" . urlencode(readable_name($key)) . "\")'>" . readable_name($key) . "</a></td>
            <td width='75px' class='table-icon' align=\"right\">
                <a class='btn btn-xs btn-default' href='javascript:;' onclick='removeFile(\"" . urlencode($key) . "\")' title='Remove this playlist'><span class='glyphicon glyphicon-remove'></span></a> 
                <a class='btn btn-xs btn-default' href='javascript:;' onclick='addFavourite(\"" . urlencode($key) . "\", \"" . urlencode(readable_name($key)) . "\", \"playlist\")' title='Favourite this playlist'><span class='glyphicon glyphicon-star'></span></a>
            </td>
            </tr>";
                    }
                    elseif(strpos($value["{DAV:}getcontenttype"], "audio") !== false) {
                        $isContent = true;
                        echo "<tr><td width='25px' class='table-icon'><span class='glyphicon glyphicon-music'></span></td>
            <td><a href='javascript:;' onclick='addToPlaylist(\"" . ($key) . "\", \"" . urlencode(readable_name($key)) . "\")'>" . readable_name($key) . "</a></td>
            <td width='75px' class='table-icon' align=\"right\">
                <a class='btn btn-xs btn-default' href='javascript:;' onclick='addFavourite(\"" . urlencode($key) . "\", \"" . urlencode(readable_name($key)) . "\", \"audio\")' title='Favourite this audio'><span class='glyphicon glyphicon-star'></span></a>
            </td>
            </tr>";
                    }
                    elseif (strpos($value["{DAV:}getcontenttype"], "video") !== false){
                        $isContent = true;
                        echo "<tr><td width='25px' class='table-icon'><span class='glyphicon glyphicon-film'></span></td>
            <td><a href='javascript:;' data-toggle=\"modal\" data-target=\"#video\" onclick='playVideo(\"" . Sabre\HTTP\encodePath($key) . "\", \"" . urlencode(readable_name($key)) . "\")'>" . readable_name($key) . "</a></td>
            <td width='75px' class='table-icon' align=\"right\">
                <a class='btn btn-xs btn-default' href='javascript:;' onclick='addFavourite(\"" . urlencode($key) . "\", \"" . urlencode(readable_name($key)) . "\", \"video\")' title='Favourite this video'><span class='glyphicon glyphicon-star'></span></a>
            </td>
            </tr>";
                    }
                }
            }
            if($isContent == false){
                echo "<tr><td>It's rather empty in here</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
    <script>
        var dataTable;
        $(document).ready(function() {
            dataTable = $('#filebrowserTable').DataTable({
                "paging":   false,
                "ordering": false,
                "bInfo" : false
            });
        } );
        $("#searchbox").on("keyup search input paste cut", function() {
            //console.log(this.value);
            dataTable.search(this.value).draw();
        });
    </script>
    <?php
}catch(Exception $e){
    echo $e->getMessage();
    echo '<hr>You shouldn\'t be here! <a href="#" onclick="getDirectories(\'' . urlencode($startFolder) . '\')">Go back to start</a> or 
    <a href="#" onclick="getDirectories(\'' . $_GET["folder"] . '\')">try loading the resource again</a>';
}
