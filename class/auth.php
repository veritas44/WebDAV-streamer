<?php

class Auth {
    var $username;
    var $users;

    function __construct($users)
    {
        $this->users = array_change_key_case($users);
    }

    function login($username, $password){
        if(key_exists(strtolower($username), $this->users)){
            if($this->users[strtolower($username)]["password_streamer"] == $password){
                $this->username = strtolower($username);
                return "success";
            }
        }
        return "failed";
    }
}