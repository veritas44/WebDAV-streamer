# Upgrade #
Okay, I mostly just push updates, without version number. To make things convenient, 
whenever I change something that requires manual work, I'll increase the version number.

**Current version: 2.2**

### From 2.1 to 2.2 ###
WebDAV streamer now has remote playing options... This requires you to execute the following SQL query:
```sql
CREATE TABLE IF NOT EXISTS `commands` (
  `id` varchar(20) NOT NULL,
  `username` varchar(1000) NOT NULL,
  `sender` int(6) NOT NULL,
  `receiver` int(6) NOT NULL,
  `command` varchar(1023) NOT NULL,
  `content` varchar(5000) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `commands`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `id` (`id`);
 ```

Also change the maximum length of `id` in the `sessions` table from 4 to 6 (or more, though that will just be overhead):
```sql
ALTER TABLE `sessions` CHANGE `id` `id` INT(6) NOT NULL;
```

Also, YouTube support has changed from a website to youtube-dl. This option should be more reliable. 
Please install youtube-dl, and add the following line to your `config.php`:

```php
const YOUTUBE_DL = 'location_of_youtube-dl';
```

### From 2.0 to 2.1 ###
Starting from version 2.1, WebDAV streamer keeps track of the sessions. Please execute the following SQL query:
```sql
CREATE TABLE IF NOT EXISTS `sessions` (
  `username` varchar(1000) NOT NULL,
  `id` int(6) NOT NULL,
  `name` varchar(1024) NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
```

### From 1.x to 2.0 ###
Starting from version 2.0, WebDAV streamer supports a library. 
This upgrade requires a few additional changes to be made:
* The folder img/album_art needs to be writable
* For current users, the library needs to be initialised. 
This can be done by either creating a new user, or by executing this SQL query in the database:

```sql
CREATE TABLE IF NOT EXISTS `library-USERPLACEHOLDER` (
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
 ```
 
 where USERPLACEHOLDER is the username you use to log in to WebDAV streamer.
 
 In order to not put an unnecessary load on the server, the library will not be automatically updated. 
 Updating can be done through the "Refresh library" item in the menu (this might take a while).