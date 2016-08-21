## WebDAV streamer ##
WebDAV streamer is a simple PHP web application for streaming music and video from a WebDAV share (like ownCloud) to the browser.
It only requires PHP and ffmpeg, and setting it up should not take too long. (Suggestions are welcome, just contact me)

**How to set it up:**

 1. Go to http://your-url/install.php
 2. Fill in the variables with your own details
 3. Enjoy

If you're upgrading WebDAV streamer (and you should), please look at UPGRADE.md.

**Comparison between Spotify and WebDAV streamer:**

Okay, you cannot really compare these two, but these are the key differences:
* You need to host WebDAV streamer yourself, Spotify is hosted for you.
* WebDAV streamer uses your own files, Spotify hosts its own music.
* Spotify costs money, WebDAV streamer is free.
* Spotify is better looking, and has more search options.
* You can use WebDAV streamer for obscure music and audio books.
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

**Why WebDAV?**

Initially, I got a 1TB private ownCloud. Great, but what more could I do with it than just storing files?
I started looking for a web audio streamer for WebDAV, but I couldn't find one, so I made one myself.
The advantage of WebDAV is that your storage is separate from your actual server.
This way you can run WebDAV streamer on a Pi without the need of a 1TB hard drive attached to it.

*But I want to use my local files!*

Well, look at a tutorial like [this (Linux)](https://www.digitalocean.com/community/tutorials/how-to-configure-webdav-access-with-apache-on-ubuntu-12-04) or [this (Windows)](http://www.iis.net/learn/install/installing-publishing-technologies/installing-and-configuring-webdav-on-iis). It's simple to set up :-)

**Automatically update library:**

You can automatically update your library. This can be done on the server or on the client that has the same files.
How it works:

*Client:*

Every step in the library script can be replaced by an argument, just use the same order.

*Server:*

Run the refresh_library.php script through the command line, with the following arguments:
`php /path/to/refresh_library.php https://my_webdavstreamer_url/ username password folder`

Keep in mind to use the URL with trailing slash. If you want, you can enter "initial" for folder to use the default folder.

Be aware that the log files might show your password in plain text!

**Requirements:**

* PHP 5.5 with curl and PDO
* A modern browser
* ffmpeg or avconv
* Some database that works with PDO. (Tested with MySQL)
* youtube-dl

**Screenshots:**

![Main screen](http://i.imgur.com/8hE2hC8.png)
![Mobile view](http://i.imgur.com/AAdW7UB.png)

If you need any assistance or have a great idea, please contact me.

Created by Koenvh - http://koenvh.nl

**Common problems:**

These are problems I've come across multiple times:
* It only displays a white page > Make sure php-curl is installed and activated.
* Some audio files don't work > Check whether they have a audio/* mime-type, and whether ffmpeg is configured correctly.
* YouTube links don't work > Check whether youtube-dl is installed and up-to-date.