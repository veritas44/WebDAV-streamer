<?php

class Database{
    var $dbh;
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
        $sql = "

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

            $stmt = $this->dbh->prepare("
            CREATE TABLE IF NOT EXISTS `library-" . $username_streamer . "` (
  `file` varchar(1000) NOT NULL,
  `last_modified` datetime NOT NULL,
  `rating` int(1) NOT NULL DEFAULT '0',
  `album` varchar(5000) NOT NULL,
  `art` varchar(5000) NOT NULL,
  `artist` varchar(5000) NOT NULL,
  `composer` varchar(5000) NOT NULL,
  `duration` int(5) NOT NULL,
  `genre` varchar(5000) NOT NULL,
  `title` varchar(5000) NOT NULL,
  `track` int(3) NOT NULL,
  `year` year(4) NOT NULL,
  PRIMARY KEY (`file`),
  UNIQUE KEY `file` (`file`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
");
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

            $stmt = $this->dbh->prepare("DROP TABLE `library-" . $name . "`");
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
            echo "favourites_exist failed.";
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

    function library_item_exist($file, $user){
        try {
            $stmt = $this->dbh->prepare("SELECT * FROM `library-" . $user . "` WHERE file = :file");
            $stmt->bindParam(':file', $file);
            $stmt->execute();

            if($stmt->rowCount() > 0) {
                return true;
            }else{
                return false;
            }
        }catch(PDOException $e){
            echo "library_item_exist failed.";
            die();
        }
    }

    function add_library_item($data, $user){
        try {
            if($this->library_item_exist($data["file"], $user)){
                $stmt = $this->dbh->prepare("UPDATE `library-" . $user . "` SET last_modified=:last_modified, album=:album, art=:art, artist=:artist, composer=:composer, duration=:duration, genre=:genre, title=:title, track=:track, year=:year WHERE file=:file");
            } else {
                $stmt = $this->dbh->prepare("INSERT INTO `library-" . $user . "` (file, last_modified, album, art, artist, composer, duration, genre, title, track, `year`)" .
                    " VALUES (:file, :last_modified, :album, :art, :artist, :composer, :duration, :genre, :title, :track, :year)");
            }
            $stmt->bindParam(':file', $data["file"]);
            $stmt->bindParam(':last_modified', $data["last_modified"]);
            $stmt->bindParam(':album', $data["album"]);
            $stmt->bindParam(':art', $data["art"]);
            $stmt->bindParam(':artist', $data["artist"]);
            $stmt->bindParam(':composer', $data["composer"]);
            $stmt->bindParam(':duration', $data["duration"]);
            $stmt->bindParam(':genre', $data["genre"]);
            $stmt->bindParam(':title', $data["title"]);
            $stmt->bindParam(':track', $data["track"]);
            $stmt->bindParam(':year', $data["year"]);
            $stmt->execute();
            return true;
        }catch(PDOException $e){
            echo "add_library_item failed.";
            die();
        }
    }

    function library_set_modified($file, $last_modified, $user){
        try {
            $stmt = $this->dbh->prepare("UPDATE `library-" . $user . "` SET last_modified=:last_modified WHERE file=:file");
            $stmt->bindParam(':file', $file);
            $stmt->bindParam(':last_modified', $last_modified);
            $stmt->execute();
        }catch(PDOException $e){
            echo "add_library_item failed.";
            die();
        }
    }

    function delete_library_item($file, $user){
        try {
            $stmt = $this->dbh->prepare("DELETE FROM `library-" . $user . "` WHERE file=:file");
            $stmt->bindParam(':file', $file);
            $stmt->execute();
        }catch (PDOException $e){
            echo "delete_library_item failed.";
            die();
        }
    }

    function library_item_modified($file, $user){
        try {
            $stmt = $this->dbh->prepare("SELECT * FROM `library-" . $user . "` WHERE file = :file");
            $stmt->bindParam(':file', $file);
            $stmt->execute();
            $stmtArray = $stmt->fetch();
            return $stmtArray["last_modified"];
        }catch (PDOException $e){
            echo "library_item_modified failed.";
            die();
        }
    }

    function get_library_albums($user){
        try {
            $stmt = $this->dbh->prepare("SELECT album, art FROM `library-" . $user . "` GROUP BY album ORDER BY album ASC");
            $stmt->execute();
            $stmtArray = $stmt->fetchAll();
            return ($stmtArray);
        }catch (PDOException $e){
            echo "get_library_albums failed.";
            die();
        }
    }

    function get_album($album, $user){
        try {
            $stmt = $this->dbh->prepare("SELECT * FROM `library-" . $user . "` WHERE album = :album ORDER BY track ASC, artist ASC");
            $stmt->bindParam(':album', $album);
            $stmt->execute();
            $stmtArray = $stmt->fetchAll();
            return $stmtArray;
        }catch (PDOException $e){
            echo "get_album failed.";
            die();
        }
    }

    function get_library_artists($user){
        try {
            $stmt = $this->dbh->prepare("SELECT album, art, artist FROM `library-" . $user . "` GROUP BY artist ORDER BY artist ASC");
            $stmt->execute();
            $stmtArray = $stmt->fetchAll();
            return ($stmtArray);
        }catch (PDOException $e){
            echo "get_library_artists failed.";
            die();
        }
    }

    function get_artist($artist, $user){
        try {
            $stmt = $this->dbh->prepare("SELECT * FROM `library-" . $user . "` WHERE artist = :artist  ORDER BY album ASC, track ASC");
            $stmt->bindParam(':artist', $artist);
            $stmt->execute();
            $stmtArray = $stmt->fetchAll();
            return $stmtArray;
        }catch (PDOException $e){
            echo "get_artist failed.";
            die();
        }
    }

    function get_library_genres($user){
        try {
            $stmt = $this->dbh->prepare("SELECT * FROM `library-" . $user . "` GROUP BY genre ORDER BY genre ASC");
            $stmt->execute();
            $stmtArray = $stmt->fetchAll();
            return ($stmtArray);
        }catch (PDOException $e){
            echo "get_library_genre failed.";
            die();
        }
    }

    function get_genre($genre, $user){
        try {
            $stmt = $this->dbh->prepare("SELECT * FROM `library-" . $user . "` WHERE genre = :genre  ORDER BY album ASC, track ASC");
            $stmt->bindParam(':genre', $genre);
            $stmt->execute();
            $stmtArray = $stmt->fetchAll();
            return $stmtArray;
        }catch (PDOException $e){
            echo "get_genre failed.";
            die();
        }
    }

    function get_library_files($user){
        try {
            $stmt = $this->dbh->prepare("SELECT file FROM `library-" . $user . "`");
            $stmt->execute();
            $stmtArray = $stmt->fetchAll();

            $flatArray = array();
            foreach($stmtArray as $item){
                $flatArray[] = $item["file"];
            }
            return $flatArray;
        }catch (PDOException $e){
            echo "get_library_files failed.";
            die();
        }
    }

    function search_library($search, $user){
        try {
            $search = "%$search%";
            $stmt = $this->dbh->prepare("SELECT * FROM `library-" . $user . "` WHERE `album` LIKE :search OR `artist` LIKE :search OR `composer` LIKE :search " .
                "OR `genre` LIKE :search OR `title` LIKE :search OR `year` LIKE :search");
            $stmt->bindParam(':search', $search);
            $stmt->execute();
            $stmtArray = $stmt->fetchAll();
            return $stmtArray;
        }catch (PDOException $e){
            echo "search_library failed.";
            die();
        }
    }
}