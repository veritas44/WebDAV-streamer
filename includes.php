<?php
session_start();
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 25-4-2016
 * Time: 12:45
 */
//Set memory limit for larger files:
ini_set("memory_limit","1600M");
ini_set('session.cookie_lifetime',84600);
ini_set('session.gc_maxlifetime',84600);

//Show all errors, because there shouldn't be any:
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

require_once("SabreDAV/vendor/autoload.php");
require_once("config.php");
require_once("class/auth.php");
require_once("class/playlist.php");
require_once("class/url_to_absolute.php");

$username = $_SESSION["username"];
$password = $_SESSION["password"];

$auth = new Auth($users);
if($auth->login($username, $password) == "success") {

} else {
    header("Location: login.php");
    die();
}
//require_once ("FFMpeg/FFMpeg.php");
//require_once ("FFMpeg/FFProbe.php");

use Sabre\DAV\Client;

$settings = array(
    'baseUri' => $users[$auth->username]['base_uri'],
    'userName' => $users[$auth->username]['username_webdav'],
    'password' => $users[$auth->username]['password_webdav']
);

$startFolder = $users[$auth->username]['start_folder'];

$client = new Client($settings);

function encodeURIComponent($str) {
    $revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
    return strtr(rawurlencode($str), $revert);
}

function readable_name($url){
    $name = $url;
    $name = basename($name);
    $name = urldecode($name);
    return $name;
}

function starts_with($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}
