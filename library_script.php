<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 31-7-2016
 * Time: 21:08
 */
set_time_limit (0);
ini_set("memory_limit", "-1");

if(php_sapi_name() != 'cli'){
    echo "This script works only when executed using the command line, for a webbased solution, see refresh_library.php";
    die();
}

echo "
db   d8b   db d88888b d8888b. d8888b.  .d8b.  db    db
88   I8I   88 88'     88  `8D 88  `8D d8' `8b 88    88
88   I8I   88 88ooooo 88oooY' 88   88 88ooo88 Y8    8P
Y8   I8I   88 88~~~~~ 88~~~b. 88   88 88~~~88 `8b  d8'
`8b d8'8b d8' 88.     88   8D 88  .8D 88   88  `8bd8' 
 `8b8' `8d8'  Y88888P Y8888P' Y8888D' YP   YP    YP   ";
echo "

.d8888. d888888b d8888b. d88888b  .d8b.  .88b  d88. d88888b d8888b.
88'  YP `~~88~~' 88  `8D 88'     d8' `8b 88'YbdP`88 88'     88  `8D
`8bo.      88    88oobY' 88ooooo 88ooo88 88  88  88 88ooooo 88oobY'
  `Y8b.    88    88`8b   88~~~~~ 88~~~88 88  88  88 88~~~~~ 88`8b  
db   8D    88    88 `88. 88.     88   88 88  88  88 88.     88 `88.
`8888Y'    YP    88   YD Y88888P YP   YP YP  YP  YP Y88888P 88   YD";

//sleep(1);

echo "\n==================================================================\n";
echo "Welcome to WebDAV streamer's library script. This script can help you generate your WebDAV streamer library. 
It will analyze all entered files and put them in a CSV file, this CSV file can be imported in the database (through phpMyAdmin for example).
Don't forget to copy the album art from the img/album_art folder.

First we'll check whether all requirements are there... If you see an error, you probably miss something.
Please keep the entire file structure the same, this is not a standalone script!
==================================================================\n";

require_once ("class/library.php");
require_once ("class/database.php");
require_once ("getid3/getid3.php");
require_once ("SabreDAV/vendor/autoload.php");
if(!in_array('fileinfo', get_loaded_extensions())){
    echo "Fileinfo missing, please enable it in php.ini";
    die();
}
//sleep(1);



echo "\n==================================================================\n";
echo "Do you see nothing between the two lines above? Then it probably worked. If it did, we can continue.
Please enter the full path to the local folder that gets synced with WebDAV, with trailing slash: (For example C:\\Users\\Bob\\Music\\)\n";
if (isset($argv[1])) {
    $fullPath = $argv[1];
    echo "Using argument. Continuing";
} else {
    $fullPath = (string)fgets(STDIN);
    $fullPath = str_replace("\n", "", $fullPath);
}

echo "Please enter where this folder can be found through WebDAV, with trailing slash: (For example /remote.php/webdav/Music/)\n";
if (isset($argv[2])) {
    $prefix = $argv[2];
    echo "Using argument. Continuing";
} else {
    $prefix = (string)fgets(STDIN);
    $prefix = str_replace("\n", "", $prefix);
}

echo "Please enter a file name for the output: (For example C:\\output.csv)\n";
if (isset($argv[3])) {
    $outputFile = $argv[3];
    echo "Using argument. Continuing";
} else {
    $outputFile = (string)fgets(STDIN);
    $outputFile = str_replace("\n", "", $outputFile);
}

if (isset($argv[3]) && isset($argv[4])){
    $url = $argv[4];
    $username = $argv[5];
    $password = $argv[6];
} elseif (!isset($argv[3])){
    echo "You can directly update the library on the server by entering the WebDAV streamer URL with trailing slash: (If you want to output to a file, leave empty):\n";
    $url = (string)fgets(STDIN);
    $url = str_replace("\n", "", $url);
    $username = "";
    $password = "";
    if(empty($url)){
        echo "Using file...";
    } else {
        echo "Please enter the username you use to log onto WebDAV streamer: \n";
        $username = (string)fgets(STDIN);
        echo "Please enter the password you use to log onto WebDAV streamer: \n";
        $password = (string)fgets(STDIN);

        $username = str_replace("\n", "", $username);
        $password = str_replace("\n", "", $password);
    }
} else {
    $url = "";
    $username = "";
    $password = "";
}

echo "\n==================================================================\n";
echo "The script will now start...\n";
//sleep(4);

$library = new Library();
$list = array();

if(empty($url)) {
    $fp = fopen($outputFile, 'w');
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$di = new RecursiveDirectoryIterator($fullPath);
foreach (new RecursiveIteratorIterator($di) as $filename => $file) {
    if(strpos(finfo_file($finfo, $filename), "audio") !== false){
        echo $filename . "\n";
        $originalFilename = str_replace($fullPath, "", $filename);
        $originalFilename = str_replace("\\", "/", $originalFilename);
        $originalFilename = $prefix . $originalFilename;
        //echo $originalFilename;

        $getTags = $library->get_tags(utf8_encode($originalFilename), utf8_encode($filename), date("F d Y H:i:s.", filemtime($filename)));
        if(empty($url)) {
            fputcsv($fp, $getTags);
        } else {
            $jsonGetTags = json_encode($getTags);
            //echo $url . "library_add.php?username=" . $username . "&password=" . $password;
            $ch = curl_init($url . "library_add.php?username=" . $username . "&password=" . $password);

            $options = array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
                CURLOPT_TIMEOUT => 120,      // timeout on response
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $jsonGetTags,
                //CURLOPT_HTTPHEADER => array(
                //    'Content-Type: application/json',
                //    'Content-Length: ' . strlen($jsonGetTags)
                //)
            );
            curl_setopt_array($ch, $options);
            echo curl_exec($ch) . "\n";
            curl_close($ch);
        }
    }
}
if(empty($url)) {
    fclose($fp);
}

