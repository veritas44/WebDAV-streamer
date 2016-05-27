<?php

class Database{
    function __construct()
    {

    }
    function connect($host, $name, $user, $pass){
        try
        {
            $this->dbh = new PDO('mysql:host=' . $host . ';dbname=' . $name, $user, $pass, array(
                PDO::ATTR_PERSISTENT => true
            ));
        } catch (PDOException $e) {
            echo "Database connection failed.";
            die();
        }
    }

    function init_database(){
        $sql = "-- phpMyAdmin SQL Dump
-- version 4.1.4
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: May 27, 2016 at 11:03 PM
-- Server version: 5.6.15-log
-- PHP Version: 5.5.8

SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";
SET time_zone = \"+00:00\";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `webdav_streamer`
--

-- --------------------------------------------------------

--
-- Table structure for table `favourites`
--

CREATE TABLE IF NOT EXISTS `favourites` (
  `username` varchar(1000) NOT NULL,
  `favourites` text NOT NULL,
  PRIMARY KEY (`username`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `username_streamer` varchar(1000) NOT NULL,
  `password_streamer` varchar(1024) NOT NULL,
  `salt` varchar(20) NOT NULL,
  `base_uri` varchar(1024) NOT NULL,
  `username_webdav` varchar(1024) NOT NULL,
  `password_webdav` varchar(1024) NOT NULL,
  `start_folder` varchar(1024) NOT NULL,
  `admin` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`username_streamer`),
  UNIQUE KEY `username_streamer` (`username_streamer`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
";

        try {
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute();
        }catch(PDOException $e){
            echo "init_database failed.";
            die();
        }
    }

    function add_user($username_streamer, $password_streamer, $base_uri, $username_webdav, $password_webdav, $start_folder, $admin = 0){
        try {
            $salt = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 20);
            $hashed_password = hash('sha256', $salt . $password_streamer);
            $username_streamer = strtolower($username_streamer);

            $stmt = $this->dbh->prepare("INSERT INTO users (username_streamer, password_streamer, salt, base_uri, username_webdav, password_webdav, start_folder, admin)" .
                                        " VALUES (:username_streamer, :password_streamer, :salt, :base_uri, :username_webdav, :password_webdav, :start_folder, :admin)");
            $stmt->bindParam(':username_streamer', $username_streamer);
            $stmt->bindParam(':password_streamer', $hashed_password);
            $stmt->bindParam(':salt', $salt);
            $stmt->bindParam(':base_uri', $base_uri);
            $stmt->bindParam(':username_webdav', $username_webdav);
            $stmt->bindParam(':password_webdav', $password_webdav);
            $stmt->bindParam(':start_folder', $start_folder);
            $stmt->bindParam(':admin', $admin);
            $stmt->execute();

            return true;
        }catch(PDOException $e){
            echo "add_user failed.";
            die();
        }
    }

    function update_user($name, $column, $new_value, $hash = false){
        if($hash){
            $salt = $this->get_data($name)["users"]["salt"];
            $new_value = hash('sha256', $salt . $new_value);
        }
        try {
            $stmt = $this->dbh->prepare("UPDATE users SET {$column} = :newvalue WHERE username_streamer=:username");
            $stmt->bindParam(':username', $name);
            $stmt->bindParam(':newvalue', $new_value);
            $stmt->execute();

            return true;
        }catch(PDOException $e){
            echo "update_user failed.";
            die();
        }
    }

    function delete_user($name){
        try {
            $stmt = $this->dbh->prepare("DELETE FROM users WHERE username_streamer=:username");
            $stmt->bindParam(':username', $name);
            $stmt->execute();
        }catch (PDOException $e){
            echo "delete_user failed.";
            die();
        }
    }

    function get_users(){
        try {
            $returnData = array();

            $stmt = $this->dbh->prepare("SELECT * FROM users");
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $returnData[$row["username_streamer"]] = $row;
            }
            return $returnData;
        }catch (PDOException $e){
            echo "get_users failed.";
            die();
        }
    }

    function favourites_exist($name){
        try {
            $stmt = $this->dbh->prepare("SELECT * FROM favourites WHERE username = :username");
            $stmt->bindParam(':username', $name);
            $stmt->execute();

            if($stmt->rowCount() > 0) {
                return true;
            }else{
                return false;
            }
        }catch(PDOException $e){
            echo "update_favourites failed.";
            die();
        }
    }

    function update_favourites($name, $favourites){
        try {
            if($this->favourites_exist($name)) {
                $stmt = $this->dbh->prepare("UPDATE favourites SET favourites=:favourites WHERE username=:username");
            } else {
                $stmt = $this->dbh->prepare("INSERT INTO favourites (username, favourites) VALUES (:username, :favourites)");
            }
            $stmt->bindParam(':username', $name);
            $stmt->bindParam(':favourites', $favourites);
            $stmt->execute();

            return true;
        }catch(PDOException $e){
            echo "update_favourites failed.";
            die();
        }
    }

    function get_data($name){
        try{

            $returnData = array();

            $stmt = $this->dbh->prepare("SELECT * FROM users WHERE username_streamer = :id");
            $stmt->bindParam(':id', $name);
            $stmt->execute();

            $stmtArray = $stmt->fetch();
            $returnData["users"] = $stmtArray;


            $stmt = $this->dbh->prepare("SELECT * FROM favourites WHERE username = :id");
            $stmt->bindParam(':id', $name);
            $stmt->execute();

            $stmtArray = $stmt->fetch();

            $returnData["favourites"] = $stmtArray;

            return $returnData;

        }catch (PDOException $e){
            echo "get_data failed.";
            die();
        }
    }
}