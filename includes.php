<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 25-4-2016
 * Time: 12:45
 */
ini_set("memory_limit","1600M");
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

require_once ("SabreDAV/vendor/autoload.php");
require_once ("config.php");
//require_once ("FFMpeg/FFMpeg.php");
//require_once ("FFMpeg/FFProbe.php");

use Sabre\DAV\Client;

$settings = array(
    'baseUri' => BASE_URI,
    'userName' => USERNAME,
    'password' => PASSWORD
);

$startFolder = START_FOLDER;

$client = new Client($settings);

function encodeURIComponent($str) {
    $revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
    return strtr(rawurlencode($str), $revert);
}