<?php

/*
 * This is now obsolete, please use the install.php to generate this file.
 * Users are now handled in the database.
$users = array(
    "bob" => array( //The username, pick anything you like. If you don't want authentication, rename bob to autologin
        "password_streamer" => "", //The password for logging into WebDAV streamer as bob, could be anything you'd like.
        "base_uri" => "", //This is the URL of your WebDAV share. For ownCloud, use your ownCloud address. Please include the http(s)://, no trailing slash
        "username_webdav" => "", //This is your username you use to access your WebDAV share
        "password_webdav" => "", //This is your password you use to access your WebDAV share
        "start_folder" => "" //This is the start folder, if your WebDAV requires some path to access the files. For ownCloud this is /remote.php/webdav/
    ) //You can add multiple users if you so desire, to do so, just add a comma here and start with another "alice" => array( etc.
);
*/

//Database connection information:
const DB_HOST = "";
const DB_NAME = "";
const DB_USERNAME = "";
const DB_PASSWORD = "";

//Please bear in mind that Windows installations require a double backslash (\\) instead of a single backslash.
const FFMPEG = "ffmpeg"; //Linux: Just use ffmpeg. Debian: avconv. Windows: Full path to ffmpeg.exe
//Please make sure the convert folder is writable (chmod 0777):
const CONVERT_FOLDER = ""; //Full system path, make sure it is reachable through your website. By default ""/your/path/to/www/output", no trailing slash
const CONVERT_FOLDER_RELATIVE = ""; //Relative to the webpage, by default "output", no trailing slash

const YOUTUBE_DL = "youtube-dl";