<?php
require_once ("includes.php");

$_SESSION['rndint'] = rand(0, 100);

$action = isset($_POST["action"]) ? $_POST["action"] : "";
$id = isset($_POST["id"]) ? $_POST["id"] : "";
$name = isset($_POST["name"]) ? $_POST["name"] : "";

$sessionID = isset($_POST["sessionID"]) ? $_POST["sessionID"] : "";
$receiverID = isset($_POST["receiverID"]) ? $_POST["receiverID"] : "";

$database = new Database();
$database->connect(DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD);

//$id = 1252;
//$name = "1252";

if($action == "update") {
    $database->add_session($id, $name, $auth->username);
}
if($action == "announce") {
    if($database->session_exist($id)){
        http_response_code(409);
    } else {
        $database->add_session($id, $name, $auth->username);
    }
}
if($action == "get") {
    echo "<div class='list-group'>";
    foreach ($database->get_sessions($auth->username) as $session){
        echo "<button type='button' class='list-group-item";
        if($receiverID == $session["id"]){
            echo " active";
        }
        echo "' onclick='changeOutput(". $session["id"] . ")'>" . $session["name"];
        if($sessionID == $session["id"]){
            echo "<span class='badge'>This device</span>";
        }
        echo "</button>";
    }
    echo "</div>";
    echo "<a href='javascript:;' class='btn btn-default' onclick='getSessions();'><span class='glyphicon glyphicon-refresh'></span> Refresh</a> ";
    echo "<a href='javascript:;' class='btn btn-default' onclick='setSessionName();'><span class='glyphicon glyphicon-pencil'></span> Edit this device's name</a> ";
    echo "<button type='button' class='btn btn-default' data-toggle='collapse' data-target='#devicesHelp'><span class='glyphicon glyphicon-question-sign'></span> Help</button> ";
    echo "<div class='collapse' id='devicesHelp'>Streaming music to another WebDAV instance is easy. Just log onto WebDAV streamer with another device, click the 'Remote play' button and select which device should play the music. The other device will display a 'Controlled remotely' message and the music will automatically start playing.</div>";
}