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
            <a href="javascript:;" class="btn btn-default blue" data-toggle="modal" data-target="#savePlaylist">Save playlist</a>
            <a href="javascript:;" class="btn btn-default blue" onclick="addAllToPlaylist()">Add all to playlist</a>
            <a href="javascript:;" class="btn btn-default blue" onclick="jPlaylist.remove()">Clear playlist</a>
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
                echo '<li><a href="#" onclick="getDirectories(\'' . urlencode($currentURL) . '\')">' . $value . '</a></li>';
            }
            ?>
        </ol>
    </div>
<?php

try {
    $folders = $client->propFind($folder, array(
        '{DAV:}getcontenttype'
    ), 1);

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
                if(!array_key_exists('{DAV:}getcontenttype', $value)){
                    $isContent = true;
                    //If there is no content length, it's likely to be a folder. This loop makes sure the folders show first.
                    echo "<tr><td width='25px' class='table-icon'><img src='img/icons/folder.png' alt='F'></td>
            <td><a href='#' onclick='getDirectories(\"" . urlencode($key) . "\")'>" . readable_name($key) . "</a></td>
            <td width='25px' class='table-icon' align=\"right\"><a href='javascript:;' onclick='addAllToPlaylist(\"" . urlencode($key) . "\")'><img src='img/icons/add.png' alt='Add'></a></td>
            </tr>";
                }
            }
            foreach($folders as $key => $value){
                if(array_key_exists('{DAV:}getcontenttype', $value)){
                    //If there is a content type, it's likely to be a file.
                    if(pathinfo(urldecode($key), PATHINFO_EXTENSION) == "pls" || pathinfo(urldecode($key), PATHINFO_EXTENSION) == "m3u" || pathinfo(urldecode($key), PATHINFO_EXTENSION) == "m3u8"){
                        $isContent = true;
                        echo "<tr><td width='25px' class='table-icon'><img src='img/icons/page_forward.png' alt='F'></td>
            <td><a href='javascript:;' data-toggle=\"modal\" data-target=\"#replacePlaylist\" onclick='setPlaylist(\"" . urlencode($key) . "\", \"" . urlencode(readable_name($key)) . "\")'>" . readable_name($key) . "</a></td>
            <td width='75px' class='table-icon' align=\"right\">
                <a href='javascript:;' onclick='removeFile(\"" . urlencode($key) . "\")'><img src='img/icons/cross.png' alt='Delete'></a> 
                <a href='javascript:;' onclick='addFavourite(\"" . urlencode($key) . "\", \"" . urlencode(readable_name($key)) . "\", \"playlist\")'><img src='img/icons/star.png' alt='Favourite'></a>
            </td>
            </tr>";
                    }
                    elseif(strpos($value["{DAV:}getcontenttype"], "audio") !== false) {
                        $isContent = true;
                        echo "<tr><td width='25px' class='table-icon'><img src='img/icons/music.png' alt='F'></td>
            <td><a href='javascript:;' onclick='addToPlaylist(\"" . urlencode($key) . "\", \"" . urlencode(readable_name($key)) . "\")'>" . readable_name($key) . "</a></td>
            <td width='75px' class='table-icon' align=\"right\">
                <a href='javascript:;' onclick='addFavourite(\"" . urlencode($key) . "\", \"" . urlencode(readable_name($key)) . "\", \"audio\")'><img src='img/icons/star.png' alt='Favourite'></a>
            </td>
            </tr>";
                    }
                    elseif (strpos($value["{DAV:}getcontenttype"], "video") !== false){
                        $isContent = true;
                        echo "<tr><td width='25px' class='table-icon'><img src='img/icons/film.png' alt='F'></td>
            <td><a href='javascript:;' data-toggle=\"modal\" data-target=\"#video\" onclick='playVideo(\"" . urlencode($key) . "\", \"" . urlencode(readable_name($key)) . "\")'>" . readable_name($key) . "</a></td>
            <td width='75px' class='table-icon' align=\"right\">
                <a href='javascript:;' onclick='addFavourite(\"" . urlencode($key) . "\", \"" . urlencode(readable_name($key)) . "\", \"video\")'><img src='img/icons/star.png' alt='Favourite'></a>
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
