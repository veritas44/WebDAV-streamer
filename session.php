<?php
require_once ("includes.php");

$_SESSION['rndint'] = rand(0, 100);

$action = isset($_POST["action"]) ? $_POST["action"] : "";
$id = isset($_POST["id"]) ? $_POST["id"] : "";
$name = isset($_POST["name"]) ? $_POST["name"] : "";

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
    echo "<ul>";
    foreach ($database->get_sessions() as $session){
        echo "<li><a href='#'>" . $session["name"] . "</a></li>";
    }
    echo "</ul>";
}