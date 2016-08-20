<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 31-7-2016
 * Time: 12:59
 */

require_once("includes.php");
session_write_close();
set_time_limit (0);
ini_set("memory_limit", "-1");

$library = new Library();
$library->connect_db();

$database = new Database();
$database->connect(DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD);

$domain = $_SERVER['HTTP_HOST'];
$prefix = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 'https://' : 'http://';

$overwrite = false;
if(isset($_GET["overwrite"]) && $_GET["overwrite"] == "1"){
    $overwrite = true;
}
$initial = false;
if(isset($_GET["initial"]) && $_GET["initial"] == "1"){
    $initial = true;
}

if($initial) {
    $RCX = new RollingCurlX(10);
}
//$data = get_file("/remote.php/webdav/Music/NFSU2/14 - Killing Joke - The Death & Resurrection Show .mp3");
//print_r($data);

//$data = $library->get_tags("/remote.php/webdav/Music/Dustforce official soundtrack/01 - Cider Time.flac");
//$data = $library->get_tags("/remote.php/webdav/Music/SimCity OST/01 - SimCity Theme.flac");
//$library->add_to_database($data);

function doPropfind($folder, $initial = false){
    global $client, $overwrite, $RCX, $prefix, $domain, $initial;

    $folders = $client->propFind($folder, array(
        '{DAV:}getcontenttype',
        '{DAV:}getlastmodified',
        '{DAV:}getcontentlength'
    ), 1);
    array_shift($folders);
    if($initial) {
        if ($overwrite) {
            $RCX->addRequest($prefix . $domain . $_SERVER['PHP_SELF'] . "?folder=" . $folder . "&overwrite=1", null, "request_done");
            //echo "<script>crawlLibrary('refresh_library.php?folder=" . $key . "&overwrite=1');</script>";
        } else {
            $RCX->addRequest($prefix . $domain . $_SERVER['PHP_SELF'] . "?folder=" . $folder, null, "request_done");
            //echo "<script>crawlLibrary('refresh_library.php?folder=" . $key . "');</script>";
        }
        foreach ($folders as $key => $value) {
            if (!array_key_exists('{DAV:}getcontenttype', $value)) {
                doPropfind($key, $initial);
            }
        }
    } else {
        foreach ($folders as $key => $value) {
            if (array_key_exists('{DAV:}getcontenttype', $value)) {
                if (strpos($value["{DAV:}getcontenttype"], "audio") !== false) {
                    if (pathinfo(urldecode($key), PATHINFO_EXTENSION) != "pls" && pathinfo(urldecode($key), PATHINFO_EXTENSION) != "m3u" && pathinfo(urldecode($key), PATHINFO_EXTENSION) != "m3u8") {
                        if ($value["{DAV:}getcontentlength"] < 100000000) { //If smaller than 100MB
                            checkValid(Sabre\HTTP\decodePath($key), $value["{DAV:}getlastmodified"], $overwrite);
                        } else {
                            echo "TOO LARGE: " . Sabre\HTTP\decodePath($key) . " (" . $value["{DAV:}getcontentlength"] . " bytes > 100000000 bytes)<br>\r\n";
                        }
                    }
                }
            }
        }
    }
}

function checkValid($file, $modified, $overwrite = false){
    global $library, $auth, $database, $client;
    $db_modified = $database->library_item_modified($file, $auth->username);
    //echo $db_modified;
    if($database->library_item_exist($file, $auth->username)) {
        if($overwrite == true){
            echo "OVERWRITE: " . $file . " (" . strtotime($db_modified) . " <> " . strtotime($modified) . ")<br>\r\n";
            //echo $file;
                $data = $library->get_file($file, $modified);
                $database->add_library_item($data, $auth->username);

        } elseif (strtotime($db_modified) < strtotime($modified)) {
            if (empty($db_modified)) {
                echo "NOTIME: " . $file . " (" . strtotime($db_modified) . " <> " . strtotime($modified) . ")<br>\r\n";
                $database->library_set_modified($file, date("Y-m-d H:i:s", strtotime($modified)), $auth->username);
            } else {
                echo "UPDATE: " . $file . " (" . strtotime($db_modified) . " <> " . strtotime($modified) . ")<br>\r\n";
                //echo $file;
                try{
                    $data = $library->get_file($file, $modified);
                    $database->add_library_item($data, $auth->username);
                } catch (\Sabre\DAV\Exception $e){
                    echo $e->getMessage();
                }

            }
        } else {
            echo "SAME: " . $file . " (" . strtotime($db_modified) . " <> " . strtotime($modified) . ")<br>\r\n";
        }
    } else {
        echo "NEW: "  . $file . " (" . strtotime($db_modified) . " <> " . strtotime($modified) . ")<br>\r\n";
        try{
            $data = $library->get_file($file, $modified);
            $database->add_library_item($data, $auth->username);
        } catch (Exception $e){
            echo $e->getMessage();
        }
    }

    //echo $file . "\r\n";

}

$fileArray = array();

function checkRemoved2($folder) {
    global $client, $fileArray;

    $folders = $client->propFind($folder, array(
        '{DAV:}getcontenttype'
    ), 1);
    array_shift($folders);
    foreach($folders as $key => $value){
        if(!array_key_exists('{DAV:}getcontenttype', $value)){
            checkRemoved2($key);
        }
        if (array_key_exists('{DAV:}getcontenttype', $value)) {
            if(strpos($value["{DAV:}getcontenttype"], "audio") !== false) {
                if(pathinfo(urldecode($key), PATHINFO_EXTENSION) != "pls" && pathinfo(urldecode($key), PATHINFO_EXTENSION) != "m3u" && pathinfo(urldecode($key), PATHINFO_EXTENSION) != "m3u8") {
                    $fileArray[] = Sabre\HTTP\decodePath($key);
                }
            }
        }
    }
}

function checkRemoved() {
    global $startFolder, $fileArray, $database, $auth;
    checkRemoved2($startFolder);
    $libraryFiles = $database->get_library_files($auth->username);
    $removed = array_diff($libraryFiles, $fileArray);
    $toAdd = array_diff($fileArray, $libraryFiles);
    //print_r($removed);
    //print_r($fileArray);
    //print_r($database->get_library_files($auth->username));
    foreach($removed as $item){
        $database->delete_library_item($item, $auth->username);
    }
    echo "Done removing items<br>\r\n";
}

function request_done($response, $url, $request_info, $user_data, $time) {
    echo $url . ": <br>\n" . $response . "<br>\n";
}

if(isset($_GET["folder"])) {
    global $startFolder;
    if ($_GET["folder"] == "initial") {
        doPropfind(Sabre\HTTP\encodePath($startFolder), $initial);
    } elseif ($_GET["folder"] == "remove") {
        checkRemoved();
    } else {
        doPropfind(Sabre\HTTP\encodePath($_GET["folder"]), $initial);
    }
    if($initial){
        //print_r($RCX->requests);
        $RCX->setTimeout(1800000000);
        $RCX->execute();
    }
    die();

} else {
    ?>
    <div class="row">
        <div class="col-md-3">
            <h3 style="margin: 10px 0;">Refresh library</h3>
        </div>
        <!--div class="col-md-9" id="refreshProgress" style="margin: 10px 0;">

        </div-->
    </div>
    <div class="row col-xs-12">
        <div class="form-group">
            <label for="refreshFolder">Folder to refresh: </label>
            <input type="text" class="form-control" id="refreshFolder" style="width: 100%" value="<?php echo (isset($_GET["refresh_folder"]) ? urldecode($_GET["refresh_folder"]) : $startFolder); ?>">
        </div>
        <div class="checkbox">
            <label>
                <input type="checkbox" id="refreshOverwrite"> Force refresh for all files
            </label>
        </div>

        <button onclick='startRefresh();' class="btn btn-primary">Click to start!</button>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div>
                <samp id='output'></samp>
            </div>
        </div>
        <!--div class="col-md-6">
            <div id="refreshCount">

            </div>
        </div-->
    </div>
    <?php
}