<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 31-7-2016
 * Time: 12:48
 */


class Library{
    var $database;

    function __construct()
    {

    }

    function connect_db(){
        $this->database = new Database();
        $this->database->connect(DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD);
    }

    function get_file($file, $last_modified){
        global $client, $library;
        $requestURL = (Sabre\HTTP\encodePath($file));
        $response = $client->request('GET', $requestURL);
        //print_r($response);
        if ($response["statusCode"] >= 400) {
            $response = $client->request('GET', Sabre\HTTP\decodePath($requestURL));
        }
        $tempnam = tempnam(CONVERT_FOLDER, "del");
        file_put_contents($tempnam, $response["body"]);
        return $this->get_tags($file, $tempnam, $last_modified, true);
    }

    function get_tags($file, $tempnam, $last_modified, $delete = false)
    {
        $getID3 = new getID3;
        $output = $getID3->analyze($tempnam);
        getid3_lib::CopyTagsToComments($output);

        $LIST_SEPERATOR = " & ";

        $data = array(
            "file" => "",
            "last_modified" => "",
            "rating" => 0,
            "album" => "",
            "art" => "",
            "artist" => "",
            "composer" => "",
            "duration" => "",
            "genre" => "",
            "title" => "",
            "track" => "",
            "year" => ""
        );

        $data["file"] = $file;
        $data["last_modified"] = date("Y-m-d H:i:s", strtotime($last_modified));

        $md5name = "";
        $coverArt = "";
        if (isset($getID3->info['id3v2']['APIC'][0]['data'])) {
            $md5name = md5($getID3->info['id3v2']['APIC'][0]['data']);
            $coverArt = $getID3->info['id3v2']['APIC'][0]['data'];
        } elseif (isset($getID3->info['id3v2']['PIC'][0]['data'])) {
            $md5name = md5($getID3->info['id3v2']['PIC'][0]['data']);
            $coverArt = $getID3->info['id3v2']['PIC'][0]['data'];
        } elseif(array_key_exists("comments", $output) && array_key_exists("picture", $output["comments"])) {
            $md5name = md5($output["comments"]["picture"][0]["data"]);
            $coverArt = $output["comments"]["picture"][0]["data"];
        }
        if(empty($md5name)){
            $data["art"] = "";
        } else {
            if(!file_exists("img/album_art/" . $md5name . ".png")) {
                file_put_contents("img/album_art/" . $md5name . ".png", $coverArt);
            }
            $data["art"] = $md5name;
        }

        if(array_key_exists("playtime_seconds", $output)) {
            $data["duration"] = $output['playtime_seconds'];
        }
        $baseArray = array();

        if (array_key_exists("comments_html", $output)) {
            $baseArray = $output["comments_html"];
        } elseif (array_key_exists("id3v2", $output)) {
            if (array_key_exists("comments", $output["id3v2"])) {
                $baseArray = $output["id3v2"]["comments"];
            }
        } elseif (array_key_exists("id3v1", $output)){
            if (array_key_exists("comments", $output["id3v1"])) {
                $baseArray = $output["id3v1"]["comments"];
            }
        }
        if(!empty($baseArray)) {
            if (array_key_exists("album", $baseArray)) {
                $data["album"] = implode($LIST_SEPERATOR, $baseArray['album']);
            }
            if (array_key_exists("artist", $baseArray)) {
                $data["artist"] = implode($LIST_SEPERATOR, $baseArray['artist']);
            }
            if (array_key_exists("composer", $baseArray)) {
                $data["composer"] = implode($LIST_SEPERATOR, $baseArray['composer']);
            }
            if (array_key_exists("genre", $baseArray)) {
                $data["genre"] = implode($LIST_SEPERATOR, $baseArray['genre']);
            }
            if (array_key_exists("title", $baseArray)) {
                $data["title"] = $baseArray['title'][0];
            } else {
                $data["title"] = basename($file);
            }
            if (array_key_exists("tracknumber", $baseArray)) {
                $data["track"] = $baseArray['tracknumber'][0];
            } elseif (array_key_exists("track_number", $baseArray)) {
                $data["track"] = $baseArray['track_number'][0];
            }
            if (array_key_exists("year", $baseArray)) {
                $data["year"] = $baseArray['year'][0];
            } elseif (array_key_exists("date", $baseArray)) {
                $data["year"] = $baseArray['date'][0];
            }
        }
        if($delete == true){
            unlink($tempnam);
        }
        //print_r($data);
        //print_r($output);
        return($data);
    }

    /*
    function add_to_database($data){
        global $auth;
        $this->database->add_library_item($data, $auth->username);
    }
    */
}