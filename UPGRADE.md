# Upgrade #

### From 1.x to 2.x ###
Starting from version 2.0, WebDAV streamer supports a library. 
This upgrade requires a few additional changes to be made:
* The folder img/album_art needs to be writable
* For current users, the library needs to be initialised. 
This can be done by either creating a new user, or by executing this SQL query in the database:

`CREATE TABLE IF NOT EXISTS `library-USERPLACEHOLDER` (
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
 ) ENGINE=MyISAM DEFAULT CHARSET=latin1;`
 
 where USERPLACEHOLDER is the username you use to log in to WebDAV streamer.
 
 In order to not put an unnecessary load on the server, the library will not be automatically updated. 
 Updating can be done through the "Refresh library" item in the menu (this might take a while).