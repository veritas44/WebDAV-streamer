<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 16-8-2016
 * Time: 21:22
 */

require_once ("includes.php");
session_write_close();

$database = new Database();
$database->connect(DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD);

$command = (isset($_POST["command"]) ? $_POST["command"] : "");
$content = (isset($_POST["content"]) ? $_POST["content"] : "");

$sender = (isset($_POST["sender"]) ? $_POST["sender"] : "");
$receiver = (isset($_POST["receiver"]) ? $_POST["receiver"] : "");

$remove = (isset($_POST["remove"]) ? $_POST["remove"] : "");

if(!empty($command) && !empty($sender) && !empty($receiver)){
    $database->add_command(uniqid(), $sender, $receiver, $command, $content, $auth->username);
} elseif(!empty($remove)){
    $database->delete_command($remove);
    $database->delete_old_commands();
} else {
    $database->delete_old_commands();
    while(count($database->get_commands($auth->username)) == 0){
        sleep(1);
    }
    echo json_encode($database->get_commands($auth->username));

}


