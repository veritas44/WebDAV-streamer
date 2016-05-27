<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 28-5-2016
 * Time: 00:14
 */
require_once ("includes.php");

use Sabre\DAV\Client;

$database = new Database();
$database->connect(DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD);

if($database->get_data($username)["users"]["admin"] != 1){
    die("Insufficient right. Please contact an admin");
}

$add_user_response = "none";

if(isset($_GET["remove"])){
    $database->delete_user($_GET["remove"]);
}

if(isset($_POST["a-username-streamer"])){
    $a_username_streamer = $_POST["a-username-streamer"];
    $a_password_streamer = $_POST["a-password-streamer"];
    $a_base_uri = $_POST["a-base-uri"];
    $a_username_webdav = $_POST["a-username-webdav"];
    $a_password_webdav = $_POST["a-password-webdav"];
    $a_start_folder = $_POST["a-start-folder"];
    $a_admin = (isset($_POST["a-admin"]) ? 1 : 0);

    try {
        $settings = array(
            'baseUri' => $a_base_uri,
            'userName' => $a_username_webdav,
            'password' => $a_password_webdav
        );

        $client = new Client($settings);

        $client->request('GET');

        $database->add_user($a_username_streamer, $a_password_streamer,$a_base_uri,$a_username_webdav,$a_password_webdav,$a_start_folder, $a_admin);

        $add_user_response = "success";
    }catch (Exception $e){
        $add_user_response =  $e->getMessage();
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
            <img src="img/logo.svg" alt="Logo" style="height: 35px; width: auto; float: left"><h4 style="vertical-align: middle;"> &nbsp;WebDAV streamer - Manage users</h4>
        </div>
        <div class="panel-body">
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation"><a href="#deleteUser" aria-controls="home" role="tab" data-toggle="tab">Delete user</a></li>
                <li role="presentation"><a href="#addUser" aria-controls="profile" role="tab" data-toggle="tab">Add user</a></li>
            </ul>
            <?php
            //echo $db_response;
            if($add_user_response != "none") {
                if($add_user_response == "success"){
                    echo '<div class="alert alert-success">User was successfully created.</div>';
                }else {
                    echo '<div class="alert alert-danger">' . $add_user_response . '</div>';
                }
            }
            ?>
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane" id="deleteUser">
                    <table class="table table-striped">
                        <thead>
                            <td>Name</td>
                            <td>Action</td>
                        </thead>
                        <tbody>
                        <?php
                            foreach ($database->get_users() as $item){
                                echo "<tr><td>" . $item["username_streamer"] . "</td>
                                <td width='75px' class='table-icon' align=\"right\"><a href='?remove=" . $item["username_streamer"] . "'><img src='img/icons/cross.png' alt='Remove'></a></td></tr>";
                            }
                        ?>
                        </tbody>
                    </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="addUser">
                    <form method="post" action="">
                        <fieldset>
                            <div class="form-group">
                                <input class="form-control" type="text" name="a-username-streamer" placeholder="Username for the streamer" />
                            </div>
                            <div class="form-group">
                                <input class="form-control" type="password" name="a-password-streamer" placeholder="Password for the streamer" />
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
                            <div class="form-group">
                                <label><input type="checkbox" name="a-admin" value="admin"/> Give administrator rights (editing users) </label>
                            </div>
                            <input type="submit" class="btn btn-md btn-block btn-success" value="Create user">
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>