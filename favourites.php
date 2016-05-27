<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 21-5-2016
 * Time: 14:30
 */
require_once ("includes.php");
$database = new Database();
$database->connect(DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD);

if($_POST["action"] == "open"){
    try {
        header('Content-Type: application/json');
        $favourites = $database->get_data($username)["favourites"]["favourites"];
        if(empty($favourites)) {
            echo "[]";
        }else{
            echo $favourites;
        }
    }  catch (Exception $e){
        echo $e->getMessage();
    }
}elseif(isset($_POST)){
    try {
        $database->update_favourites($username, file_get_contents('php://input'));
        //file_put_contents(FAVOURITES_FOLDER . $fileFriendlyUsername, file_get_contents('php://input'));
        echo "success";
    } catch (Exception $e){
        echo $e->getMessage();
    }
}else{
    echo "No action specified";
}