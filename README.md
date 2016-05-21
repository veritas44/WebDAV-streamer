## WebDAV streamer ##
WebDAV streamer is a simple PHP web application for streaming music and video from a WebDAV share (like ownCloud) to the browser.
It only requires PHP and ffmpeg, and setting it up should not take too long.

**How to set it up:**

 1. Rename config.example.php to config.php
 2. Fill in the variables with your own details
 3. Chmod the ffmpeg folder to allow ffmpeg to write data to it.
 4. Chmod the favourites folder to allow writing
 5. Enjoy

**Comparison between Spotify and WebDAV streamer:**

Okay, you cannot really compare these two, but these are the key differences:
* You need to host WebDAV streamer yourself, Spotify is hosted for you.
* WebDAV streamer uses your own files, Spotify hosts its own music.
* Spotify costs money, WebDAV streamer is free.
* Spotify is better looking, and has more search options.
* You can use WebDAV streamer for obscure music and audiobooks.
* A lot more...

Basically, if you already digitalised your CDs and put them nice and orderly in folders,
WebDAV streamer can be the solution. You can use your own files, own structure.
If you want to have nearly all music available at all times, without worrying about updating or anything,
get Spotify.

**What does it work with?**

Nearly every cloud storage that provides WebDAV access... A few examples:
* ownCloud (including all ownCloud hosters like STACK)
* Box.net
* Cloud Drive
* CloudMe
* CloudSafe
* DriveHQ
* MyDrive
* Pydio
* Storage Made Easy
* Strato HiDrive
* Synology NASes
* And many more...

If it supports WebDAV, it will probably work. (If it doesn't, please contact me.)

Please bear in mind that running this with a bandwidth limiter is not recommended,
because it first downloads the file, and then streams it, so the bandwidth used will be double the file size.

**Requirements:**

* PHP 5.5 with curl and sqlite3
* A modern browser
* ffmpeg or avconv

If you need any assistance or have a great idea, please contact me.

Created by Koenvh - http://koenvh.nl