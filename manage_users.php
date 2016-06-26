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
    die("<h3 style='margin: 10px'>Insufficient rights. Please contact an admin</h3>");
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
<div class="row">
    <div class="col-md-3">
        <h3>Manage users</h3>
    </div>
    </div>
<div class="col-md-8">
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation"><a href="#deleteUser" aria-controls="home" role="tab" data-toggle="tab">Delete user</a></li>
        <li role="presentation"><a href="#addUser" aria-controls="profile" role="tab" data-toggle="tab">Add user</a></li>
    </ul>
    <?php
    //echo $db_response;
    if($add_user_response != "none") {
        if($add_user_response == "success"){
            echo '<div class="alert alert-success">User was successfully created.<br><a href="index.php">Go back to WebDAV streamer</a></div>';
        }else {
            echo '<div class="alert alert-danger">' . $add_user_response . '<br><a href="index.php">Go back to WebDAV streamer</a></div>';
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
                                <td width='75px' class='table-icon' align=\"right\"><a href='?remove=" . $item["username_streamer"] . "'><span class='glyphicon glyphicon-remove'></span></a></td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
        <div role="tabpanel" class="tab-pane" id="addUser">
            <form method="post" action="manage_users.php">
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