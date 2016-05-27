<?php

class Auth {
    var $username;
    var $database;
    var $userData;

    function __construct()
    {
        $this->database = new Database();
        $this->database->connect(DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD);
    }

    function login($username, $password){
        $this->userData = $userData = $this->database->get_data(strtolower($username));
        $hashed_password = hash('sha256', $userData["users"]["salt"] . $password);

        if($userData["users"]["password_streamer"] == $hashed_password){
            $this->username = strtolower($username);
            return "success";
        } else {
            return "failed";
        }
    }
}