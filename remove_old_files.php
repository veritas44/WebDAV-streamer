<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 26-4-2016
 * Time: 15:02
 */
require_once ("includes.php");

if (file_exists(CONVERT_FOLDER)) {
    foreach (new DirectoryIterator(CONVERT_FOLDER) as $fileInfo) {
        if ($fileInfo->isDot()) {
            continue;
        }
        //Delete all files older than an hour
        if (time() - $fileInfo->getCTime() >= 60 * 60) {
            unlink($fileInfo->getRealPath());
        }
    }
} 
