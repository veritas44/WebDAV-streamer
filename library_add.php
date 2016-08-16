<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 9-8-2016
 * Time: 16:02
 */
$username = $_GET["username"];
$password = $_GET["password"];

require_once("class/auth.php");
require_once("class/database.php");
require_once("config.php");

$database = new Database();
$database->connect(DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD);

$auth = new Auth();
if($auth->login($username, $password) == "success") {

} else {
    echo 'ERROR: FAILED AUTH';
    die();
}

$data = json_decode(file_get_contents('php://input'), true);
if(!empty($data)) {
    if ($database->library_item_exist($data["file"], $auth->username)) {
        if (strtotime($database->library_item_modified($data["file"], $auth->username)) < strtotime($data["last_modified"])) {
            $database->add_library_item($data, $auth->username);
            echo 'UPDATE: ' . $data["file"];
        } else {
            echo 'SAME: ' . $data["file"];
        }
    } else {
        $database->add_library_item($data, $auth->username);
        echo 'NEW: ' . $data["file"];
    }
} else {
    echo 'EMPTY REQUEST';
}
