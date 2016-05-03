<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 4-5-2016
 * Time: 01:02
 */

require_once ("includes.php");

$requestURL = (($_GET["file"]));

$response = $client->request("DELETE", $requestURL);
if($response["statusCode"] < 400){
    //Nothing yet, but this is success.
} else {
    //This is a fail.
}