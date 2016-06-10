<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 27-5-2016
 * Time: 19:03
 */
session_start();

use Sabre\DAV\Client;

require_once("SabreDAV/vendor/autoload.php");
require_once("class/database.php");

$db_response = "none";
$db_success = false;
$db_host = (isset($_SESSION["db_host"]) ? $_SESSION["db_host"] : "");
$db_name = (isset($_SESSION["db_name"]) ? $_SESSION["db_name"] : "");
$db_username = (isset($_SESSION["db_username"]) ? $_SESSION["db_username"] : "");
$db_password = (isset($_SESSION["db_password"]) ? $_SESSION["db_password"] : "");

if(file_exists("config.php")){
    $db_success = true;
    $ff_success = true;

    include_once("config.php");
    $database = new Database();
    $database->connect(DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD);
    if(count($database->get_users()) > 0){
        die("WebDAV streamer is already installed.");
    }
}
if(isset($_POST["db-host"])) {
    try {
        $db_host = $_SESSION["db_host"] = $_POST["db-host"];
        $db_name = $_SESSION["db_name"] = $_POST["db-name"];
        $db_username = $_SESSION["db_username"] = $_POST["db-username"];
        $db_password = $_SESSION["db_password"] = $_POST["db-password"];

        $dbh = new PDO('mysql:host=' . $db_host . ';dbname=' . $db_name, $db_username, $db_password);

        $db_success = true;
        $db_response = "success";
    } catch (PDOException $e) {
        $db_response = $e->getMessage();
    }
}
$ff_response = "none";
$ff_success = false;
$ffmpeg = (isset($_SESSION["ff_ffmpeg"]) ? $_SESSION["ff_ffmpeg"] : "");
$convert_folder = (isset($_SESSION["ff_convert_folder"]) ? $_SESSION["ff_convert_folder"] : "");
$convert_folder_relative = (isset($_SESSION["ff_convert_folder_relative"]) ? $_SESSION["ff_convert_folder_relative"] : "");

if(isset($_POST["ff-ffmpeg"])){
    $ffmpeg = $_SESSION["ff_ffmpeg"] = $_POST["ff-ffmpeg"];
    $convert_folder = $_SESSION["ff_convert_folder"] = $_POST["ff-convert-folder"];
    $convert_folder_relative = $_SESSION["ff_convert_folder_relative"] = $_POST["ff-convert-folder-relative"];

    $folder_writable = true;

    try {
        $testfile = fopen($convert_folder . DIRECTORY_SEPARATOR . "writing.test", "w") or $folder_writable = false;
        fclose($testfile);
    } catch (Exception $e) {
        $folder_writable = false;
    }
    if($folder_writable) {
        try {
            if(file_exists($convert_folder . DIRECTORY_SEPARATOR  . "demo.mp3")) {
                unlink($convert_folder . DIRECTORY_SEPARATOR . "demo.mp3");
            }
            echo shell_exec($ffmpeg . " -i " . $convert_folder . DIRECTORY_SEPARATOR . "demo.flac -threads 0 -aq 3 -map_metadata 0 -id3v2_version 3 -vn " . $convert_folder . DIRECTORY_SEPARATOR . "demo.mp3");
            $ff_response = "success";
            $ff_success = true;
        } catch (Exception $e) {
            $ff_response = $e->getMessage();
        }
    }else{
        $ff_response = "Output folder not writable! Please make sure the webserver user has the permission to write to the output folder.";
    }
}

$config_response = "none";
if(isset($_POST["create-config"])){
    $config_writable = true;
    $config = fopen("config.php", "w") or $config_writable = false;
    $config_content =   "<?php\n" .
        "const DB_HOST = '$db_host';\n" .
        "const DB_NAME = '$db_name';\n" .
        "const DB_USERNAME = '$db_username';\n" .
        "const DB_PASSWORD = '$db_password';\n\n" .
        "const FFMPEG = '$ffmpeg';\n" .
        "const CONVERT_FOLDER = '$convert_folder';\n" .
        "const CONVERT_FOLDER_RELATIVE = '$convert_folder_relative';\n";
    //$config_writable = false;
    if($config_writable) {
        fwrite($config, $config_content);
        fclose($config);
        $config_response = "success";
    }else{
        $config_response = "Config not writable! Please copy the following code to a file config.php in the root directory:<br>
                            <textarea style='width: 100%' readonly>$config_content</textarea>";
    }
}

$add_user_response = "none";
if(isset($_POST["a-username-streamer"])){
    $a_username_streamer = $_POST["a-username-streamer"];
    $a_password_streamer = $_POST["a-password-streamer"];
    $a_password_streamer2 = $_POST["a-password-streamer2"];
    $a_base_uri = $_POST["a-base-uri"];
    $a_username_webdav = $_POST["a-username-webdav"];
    $a_password_webdav = $_POST["a-password-webdav"];
    $a_start_folder = $_POST["a-start-folder"];

    if($a_password_streamer == $a_password_streamer2) {
        try {
            $settings = array(
                'baseUri' => $a_base_uri,
                'userName' => $a_username_webdav,
                'password' => $a_password_webdav
            );

            $client = new Client($settings);

            $response = $client->request("GET", Sabre\HTTP\encodePath($a_start_folder));

            if ($response["statusCode"] >= 400) {
                $add_user_response = "WebDAV authentication went wrong";
            } else {

                require_once("config.php");

                $database = new Database();
                $database->connect(DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD);
                $database->init_database();
                $database->add_user($a_username_streamer, $a_password_streamer, $a_base_uri, $a_username_webdav, $a_password_webdav, $a_start_folder, 1);

                $add_user_response = "success";
            }
        } catch (Exception $e) {
            $add_user_response = $e->getMessage();
        }
    } else {
        $add_user_response = "Passwords do not match!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>WebDAV streamer</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel='shortcut icon' type='image/x-icon' href='favicon.ico' />
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <script src="js/jquery-2.2.3.js"></script>
    <script src="js/bootstrap.js"></script>
    <style>
        html, body {
            height: 100%;
            width: 100%;
            background: #e6e6e6;
        }
        .container {
            position: absolute;
            width: 800px;
            top: 50%;
            left: 50%;
            -webkit-transform: translate(-50%, -50%);
            -ms-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
        }

        @media (max-width: 800px) {
            .container {
                width: 100%;
            }

            .col-md-4 {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="panel panel-default">
            <div class="panel-heading">
                <img src="img/logo.svg" alt="Logo" style="height: 35px; width: auto; float: left"><h4 style="vertical-align: middle;"> &nbsp;WebDAV streamer - Install wizard</h4>
            </div>
            <div class="panel-body tab-content">
                <div role="tabpanel" class="tab-pane active" id="step1">
                    <?php
                    if (version_compare(phpversion(), "5.5.0", "<")) {
                        echo '<div class="alert alert-danger">Your PHP version is not supported! Please upgrade to PHP 5.5 or higher</div>';
                    }
                    if (function_exists('curl_version') == false){
                        echo '<div class="alert alert-danger">Your PHP installation does not have cURL installed! Please install cURL to continue</div>';
                    }
                    ?>
                    <p>Welcome to WebDAV streamer, your WebDAV audio streamer. <br>WebDAV streamer is a simple PHP web application for streaming music and video from a WebDAV share (like ownCloud) to the browser.
                        It only requires PHP and ffmpeg, and setting it up should not take too long.</p>
                    <hr>
                    <a href="#step2" role="tab" data-toggle="tab" class="nav-tabs btn blue wizard" style="float: right;">Next</a>
                </div>
                <div role="tabpanel" class="tab-pane" id="step2">
                    <p>WebDAV streamer requires a database to operate. Please enter your database details:</p>
                    <form method="post" action="">
                        <?php
                        //echo $db_response;
                        if($db_response != "none") {
                            if($db_response == "success"){
                                echo '<div class="alert alert-success">Database connection was successfully established</div>';
                            }else {
                                echo '<div class="alert alert-danger">Could not connect to the database! <br>' . $db_response . '</div>';
                            }
                        }
                        ?>
                        <fieldset>
                            <div class="form-group">
                                <input class="form-control" type="text" name="db-host" placeholder="Database host" value="<?php echo $db_host; ?>"/>
                            </div>
                            <div class="form-group">
                                <input class="form-control" type="text" name="db-name" placeholder="Database name" value="<?php echo $db_name; ?>"/>
                            </div>
                            <div class="form-group">
                                <input class="form-control" type="text" name="db-username" placeholder="Database username" value="<?php echo $db_username; ?>"/>
                            </div>
                            <div class="form-group">
                                <input class="form-control" type="password" name="db-password" placeholder="Database password" value="<?php echo $db_password; ?>" />
                            </div>
                            <input type="submit" class="btn btn-md btn-block btn-success" value="Test & save connection">
                        </fieldset>
                    </form>
                    <hr>
                    <a href="#step1" role="tab" data-toggle="tab" class="nav-tabs btn blue wizard">Previous</a>
                    <a href="#step3" role="tab" data-toggle="tab" class="nav-tabs btn blue wizard" style="float: right;" <?php echo ($db_success ? "" : "disabled"); ?>>Next</a>
                </div>
                <div role="tabpanel" class="tab-pane" id="step3">
                    <p>WebDAV streamer also requires ffmpeg (or avconv) to function:</p>
                    <form method="post" action="">
                        <?php
                        //echo $db_response;
                        if($ff_response != "none") {
                            if($ff_response == "success"){
                                echo    '<div class="alert alert-info">Please try to play the following audio file to verify that FFmpeg works:' .
                                        '<audio src="' . $convert_folder_relative . '/demo.mp3" controls autoplay></audio></div>';
                            }else {
                                echo '<div class="alert alert-danger">' . $ff_response . '</div>';
                            }
                        }
                        ?>
                        <fieldset>
                            <div class="form-group">
                                <input class="form-control" type="text" name="ff-ffmpeg" placeholder="FFmpeg executable (for Linux, just use 'ffmpeg')" value="<?php echo $ffmpeg; ?>"/>
                            </div>
                            <div class="form-group">
                                <input class="form-control" type="text" name="ff-convert-folder" placeholder="Full path to the output folder (eg. '/var/www/output' or 'C:\\Web\\output')" value="<?php echo $convert_folder; ?>"/>
                            </div>
                            <div class="form-group">
                                <input class="form-control" type="text" name="ff-convert-folder-relative" placeholder="Relative path to the output folder (eg. 'output')" value="<?php echo $convert_folder_relative; ?>"/>
                            </div>
                            <input type="submit" class="btn btn-md btn-block btn-success" value="Test & save settings">
                        </fieldset>
                    </form>
                    <hr>
                    <a href="#step2" role="tab" data-toggle="tab" class="nav-tabs btn blue wizard">Previous</a>
                    <a href="#step4" role="tab" data-toggle="tab" class="nav-tabs btn blue wizard" style="float: right;" <?php echo ($ff_success ? "" : "disabled"); ?>>Next</a>
                </div>
                <div role="tabpanel" class="tab-pane" id="step4">
                    <p>Now we will generate a config.php</p>
                    <form method="post" action="">
                        <?php
                        //echo $db_response;
                        if($config_response != "none") {
                            if($config_response == "success"){
                                echo '<div class="alert alert-success">Config file was successfully created</div>';
                            }else {
                                echo '<div class="alert alert-warning">' . $config_response . '</div>';
                            }
                        }
                        ?>
                        <fieldset>
                            <!--
                            <input type="text" name="c-db-host">
                            <input type="hidden" name="c-db-name">
                            <input type="hidden" name="c-db-username">
                            <input type="hidden" name="c-db-password">

                            <input type="hidden" name="c-ff-ffmpeg">
                            <input type="hidden" name="c-ff-convert-folder">
                            <input type="hidden" name="c-ff-convert-folder-relative">
                            -->

                            <input type="hidden" name="create-config" value="1">
                            <input type="submit" class="btn btn-md btn-block btn-success" value="Create config">
                        </fieldset>
                    </form>
                    <hr>
                    <a href="#step3" role="tab" data-toggle="tab" class="nav-tabs btn blue wizard">Previous</a>
                    <a href="#step5" role="tab" data-toggle="tab" class="nav-tabs btn blue wizard" style="float: right;">Next</a>
                </div>
                <div role="tabpanel" class="tab-pane" id="step5">
                    <p>Lastly, we will need to add a user:</p>
                    <form method="post" action="">
                        <?php
                        //echo $db_response;
                        if($add_user_response != "none") {
                            if($add_user_response == "success"){
                                echo '<div class="alert alert-success">User was successfully created. Please remove the install.php file, and enjoy WebDAV streamer.</div>';
                            }else {
                                echo '<div class="alert alert-danger">' . $add_user_response . '</div>';
                            }
                        }
                        ?>
                        <fieldset>
                            <div class="form-group">
                                <input class="form-control" type="text" name="a-username-streamer" placeholder="Username for the streamer" />
                            </div>
                            <div class="form-group">
                                <input class="form-control" type="password" name="a-password-streamer" placeholder="Password for the streamer" />
                            </div>
                            <div class="form-group">
                                <input class="form-control" type="password" name="a-password-streamer2" placeholder="Password for the streamer (repeat)" />
                            </div>
                            <hr>
                            <div class="form-group">
                                <input class="form-control" type="text" name="a-base-uri" placeholder="WebDAV server (eg. https://webdav.example.com, no trailing slash and include the http(s)://)" />
                            </div>
                            <div class="form-group">
                                <input class="form-control" type="text" name="a-username-webdav" placeholder="Username used to sign in to WebDAV" />
                            </div>
                            <div class="form-group">
                                <input class="form-control" type="password" name="a-password-webdav" placeholder="Password used to sign in to WebDAV" />
                            </div>
                            <div class="form-group">
                                <input class="form-control" type="text" name="a-start-folder" placeholder="If your WebDAV server requires an additional path to work. For ownCloud this is /remote.php/webdav/" />
                            </div>
                            <input type="submit" class="btn btn-md btn-block btn-success" value="Create user">
                        </fieldset>
                    </form>
                    <hr>
                    <a href="#step4" role="tab" data-toggle="tab" class="nav-tabs btn blue wizard">Previous</a>
                    <!--a href="index.php" class="nav-tabs btn blue wizard" style="float: right;">Go to home</a-->
                </div>
            </div>
        </div>
    </div>
    <script>
        $(function(){
            var hash = window.location.hash;
            hash && $('a[href="' + hash + '"]').tab('show');

            $('a.nav-tabs').click(function (e) {
                console.log(this.hash);
                $(this).tab('show');
                var scrollmem = $('body').scrollTop() || $('html').scrollTop();
                window.location.hash = this.hash;
                $('html,body').scrollTop(scrollmem);
            });
        });

        $('[name^=db-]').change(function() {
            localStorage.setItem($(this).attr("name"), $('[name=c-' + $(this).attr("name") + ']').val($(this).val()));
        });
        $('[name^=db-]').change(function() {
            localStorage.setItem($(this).attr("name"), $('[name=c-' + $(this).attr("name") + ']').val($(this).val()));
        });
        /*
        $('[name^=ff-]').change(function() {
            console.log($(this).attr("name"));
            $('[name=c-' + $(this).attr("name") + ']').val($(this).val());
        });
        */
    </script>
</body>
</html>