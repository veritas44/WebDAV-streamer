<?php

class Auth {
    var $username;
    var $users;

    function __construct($users)
    {
        $this->users = $users;
    }
    function login($username, $password){
        if(key_exists($username, $this->users)){
            if($this->users[$username]["password_streamer"] == $password){
                $this->username = $username;
                return "success";
            }
        }
        return "failed";
    }
}