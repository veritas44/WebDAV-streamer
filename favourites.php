<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 21-5-2016
 * Time: 14:30
 */
require_once ("includes.php");
$fileFriendlyUsername = preg_replace("/[^a-zA-Z0-9]+/", "", $auth->username);
const FAVOURITES_FOLDER = "./favourites/";

if($_POST["action"] == "open"){
    try {
        header('Content-Type: application/json');
        if(file_exists(FAVOURITES_FOLDER . $fileFriendlyUsername)) {
            echo file_get_contents(FAVOURITES_FOLDER . $fileFriendlyUsername);
        } else {
            $newFile = fopen(FAVOURITES_FOLDER . $fileFriendlyUsername, "w");
            fwrite($newFile, "[]");
            fclose($newFile);
            echo "[]";
        }
    }  catch (Exception $e){
        echo $e->getMessage();
    }
}elseif(isset($_POST)){
    try {
        file_put_contents(FAVOURITES_FOLDER . $fileFriendlyUsername, file_get_contents('php://input'));
        echo "success";
    } catch (Exception $e){
        echo $e->getMessage();
    }
}else{
    echo "No action specified";
}