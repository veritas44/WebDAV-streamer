<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 24-6-2016
 * Time: 15:26
 */
require_once ("includes.php");

$password_change_response = "none";
if(isset($_POST["newpass1"])){
    $newpass1 = $_POST["newpass1"];
    $newpass2 = $_POST["newpass2"];

    if($newpass1 == $newpass2){
        $database = new Database();
        $database->connect(DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD);

        $database->update_user($username, "password_streamer", $newpass1, true);
        $password_change_response = "Successfully updated the password";
        header("Refresh:0");
        die();
    } else {
        $password_change_response = "Passwords did not match! <script>alert('Passwords did not match!');</script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>WebDAV streamer</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="css/loader.css">
    <link rel="stylesheet" type="text/css" href="css/ionicons.css">
    <link rel="stylesheet" type="text/css" href="jplayer/skin/webdav/style.css">
    <!--link rel="stylesheet" type="text/css" href="jplayer/skin/foundation/css/jplayer.blue.monday.css"-->
    <link rel="stylesheet" type="text/css" href="css/style.css">

    <link rel="apple-touch-icon" sizes="57x57" href="apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="manifest" href="manifest.json">
    <meta name="msapplication-TileColor" content="#2b3e50">
    <meta name="msapplication-TileImage" content="ms-icon-144x144.png">
    <meta name="theme-color" content="#2b3e50">
</head>
<body>
<div class="remote-connect" id="remoteConnected" style="display: none"><span class="remote-connect-text">Controlled remotely. <a href="#" onclick="disconnectMaster();">Disconnect</a>.</span></div>
<div class="row full-height" style="width: 100%;">
    <div class="full-height menu-left">
        <nav class="menu-left navbar navbar-default" role="navigation">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="menu-left navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#">WebDAV streamer</a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse menu-left navbar-collapse navbar-ex1-collapse">
                <ul class="menu-left nav navbar-nav">
                    <li><a href="#" onclick="loadPage('albums.php')"><span class="glyphicon glyphicon-cd"></span> Albums</a></li>
                    <li><a href="#" onclick="loadPage('artists.php')"><span class="glyphicon glyphicon-headphones"></span> Artists</a></li>
                    <li><a href="#" onclick="loadPage('genres.php')"><span class="glyphicon glyphicon-sunglasses"></span> Genres</a></li>
                    <li class="active"><a href="#" onclick="getDirectories(currentDirectory)"><span class="glyphicon glyphicon-file"></span> Files</a></li>
                    <li><a href="#" onclick="loadPage('youtube.php')"><span class="glyphicon glyphicon-expand"></span> YouTube</a></li>
                    <li><a href="#" onclick="loadPage('favourites.php')"><span class="glyphicon glyphicon-star"></span> Favourites</a></li>
                    <li><a href="#" onclick="loadPage('db_search.php')"><span class="glyphicon glyphicon-search"></span> Search library</a></li>
                    <li><a href="#" onclick="loadPage('refresh_library.php')"><span class="glyphicon glyphicon-refresh"></span> Refresh library</a></li>
                    <li><a href="#" onclick="loadPage('manage_users.php')"><span class="glyphicon glyphicon-user"></span> Manage users</a></li>
                    <li><a href="#" onclick="loadPage('change_password.php')"><span class="glyphicon glyphicon-pencil"></span> Change password</a></li>
                    <li><a href="#" onclick="window.location.href = 'login.php?logout=1';"><span class="glyphicon glyphicon-off"></span> Log out</a></li>
                    <li><a href="#" onclick="loadPage('about.php')"><span class="glyphicon glyphicon-info-sign"></span> About</a></li>
                    <!-- Don't worry, the likelihood that this'll be included is very, very small, and now that you've found it, you could easily remove it anyway :-) -->
                    <!--li><a href="https://youtu.be/skW9ATNDfL4" style="text-align: center"><img src="http://3dprintingindustry.com/wp-content/uploads/2014/05/Piracy-1.jpg?611f67" style="height: 250px; width: 250px;"></a> </li-->
                </ul>
                <img id="preloadAudio" src="" style="display:none">
            </div><!-- /.navbar-collapse -->
        </nav>
    </div>
    <div class="full-height" id="content">
        <!--div class="loader-background"><div class="loader">Loading&#8230;</div></div-->
    </div>
    <div class="full-height" style="overflow-y: hidden; background-color: black;" id="video">
            <video id="videoPlayer" style="width: 100%; height: 100%;" controls src='' autoplay></video>
        <textarea id="videoProgress" class="shell" style="height: 50px; width: 100%; display: none;" readonly></textarea>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="savePlaylist">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Please enter a name for this playlist:</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <input type="text" placeholder="Enter a name..." id="playlistName" class="form-control input-sm">
                </div>
                <div class="form-group">
                    <select id="playlistType" class="form-control">
                        <option value="pls">PLS</option>
                        <option value="m3u">M3U</option>
                    </select>
                </div>
                <div class="form-group">
                    <button onclick="savePlaylist()" data-close class="btn btn-primary">Save playlist</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="replacePlaylist">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">What do you want to do with this playlist?</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <button class="btn btn-primary" data-dismiss="modal" onclick="openPlaylist(playlistFile, playlistName, true)">Replace current</button>
                    <button class="btn btn-primary" data-dismiss="modal" onclick="openPlaylist(playlistFile, playlistName, false)">Add to current</button>
                    <!--button class="btn" data-dismiss="modal">Cancel</button-->
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="devices">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Choose a device to stream to:</h4>
            </div>
            <div class="modal-body" id="devicesContent">
                <div class="loader"></div>
            </div>
        </div>
    </div>
</div>

<div class="footer-whitespace"></div>
<nav class="navbar navbar-default navbar-fixed-bottom">
    <div id="jquery_jplayer_1" class="jp-jplayer"></div>
    <div id="jp_container_1" class="jp-audio">
        <div class="jp-type-playlist">
            <div class="jp-gui jp-interface">
                <ul class="jp-controls">
                    <li>
                        <div class="jp-progress">
                            <div class="buffer-bar progress-bar progress-bar-striped active" style="width: 100%"></div>
                            <div class="jp-seek-bar progress">
                                <div class="jp-play-bar progress-bar"></div>
                            </div>
                        </div>
                    </li>
                    <li><a href="javascript:;" class="jp-previous btn btn-default btn-sm btn-music" tabindex="1"><span class="glyphicon glyphicon-backward"></span></a></li>
                    <li><a href="javascript:;" class="jp-play btn btn-default btn-sm btn-music" tabindex="1"><span class="glyphicon glyphicon-play"></span></a></li>
                    <li><a href="javascript:;" class="jp-pause btn btn-default btn-sm btn-music" tabindex="1"><span class="glyphicon glyphicon-pause"></span></a></li>
                    <li><a href="javascript:;" class="jp-next btn btn-default btn-sm btn-music" tabindex="1"><span class="glyphicon glyphicon-forward"></span></a></li>
                    <li><a href="javascript:;" class="jp-stop btn btn-default btn-sm btn-music" tabindex="1"><span class="glyphicon glyphicon-stop"></span></a></li>

                    <li><a href="javascript:;" class="jp-shuffle btn btn-default btn-sm btn-music" tabindex="1" title="Shuffle the playlist"><span class="glyphicon glyphicon-random"></span></a></li>
                    <li><a href="javascript:;" class="jp-shuffle-off btn btn-default btn-sm btn-music orange" tabindex="1" title="Unshuffle the playlist"><span class=" glyphicon glyphicon-random"></span></a></li>
                    <li><a href="javascript:;" class="jp-repeat btn btn-default btn-sm btn-music" tabindex="1" title="Repeat"><span class="glyphicon glyphicon-repeat"></span></a></li>
                    <li><a href="javascript:;" class="jp-repeat-off btn btn-default btn-sm btn-music orange" tabindex="1" title="Repeat off"><span class="glyphicon glyphicon-repeat"></span></a></li>

                    <li><span class="jp-time"><span class="jp-current-time"></span> / <span  class="jp-duration"></span></span></li>

                    <li><a href="javascript:;" class="jp-mute btn btn-default btn-sm btn-music" tabindex="1" title="Mute"><span class="glyphicon glyphicon-volume-down"></span></a></li>
                    <li><a href="javascript:;" class="jp-unmute btn btn-default btn-sm btn-music" tabindex="1" title="Unmute"><span class="glyphicon glyphicon-volume-off"></span></a></li>
                    <li>
                        <div class="jp-volume-bar progress" style="display: inline-block">
                            <div class="jp-volume-bar-value progress-bar"></div>
                        </div>
                    </li>
                    <!--li><a href="javascript:;" class="jp-volume-max btn btn-default btn-sm btn-music" tabindex="1" title="Max volume"><span class="glyphicon glyphicon-volume-up"></span></a></li-->
                    <li><a href="javascript:;" class="show-playlist btn btn-default btn-sm btn-music" tabindex="1" title="Show playlist"><span class="glyphicon glyphicon-list"></span></a></li>
                    <li><a href="javascript:;" class="playlist-controls btn btn-default btn-sm" onclick="jPlaylist.shuffle(true, false);">Reshuffle</a></li>
                    <li><a href="javascript:;" class="playlist-controls btn btn-default btn-sm" data-toggle="modal" data-target="#savePlaylist">Save playlist</a></li>
                    <li><a href="javascript:;" class="playlist-controls btn btn-default btn-sm show-playlist" onclick="jPlaylist.remove()">Clear playlist</a></li>
                    <li><button type="button" class="playlist-controls btn btn-default btn-sm" data-toggle="modal" data-target="#devices" onclick="getSessions();" id="devicesButton">Devices</button></li>
                    <li><span id="added" style="display: none;">Added</span></li>
                    <li id="playInfo"></li>
                </ul>

            </div>
            <div class="playlist-container">
                <table class="jp-playlist table table-striped table-hover" id="jp-playlist" style="display: none">
                    <tr></tr>
                </table>
            </div>

            <div class="jp-no-solution">
                <span>Update Required</span>
                To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
            </div>
        </div>
    </div>
    <div>
    </div>
</nav>


<script src="js/jquery-2.2.3.js"></script>
<script src="js/bootstrap.js"></script>

<script src="jplayer/jquery.jplayer.min.js"></script>
<script src="jplayer/jplayer.playlist.js"></script>

<script src="js/jsmediatags.js"></script>

<script src="js/RowSorter.js"></script>
<script src="js/playlist.js"></script>

<script src="js/index.js"></script>
<script src="js/interface.js"></script>
<script src="js/filebrowser.js"></script>
<script src="js/remote-play.js"></script>

<script>
    var currentUser = "";
    var defaultDirectory = "";
    var outputDirectory = "";
    $(document).ready(function () {
        currentUser = "<?php echo preg_replace("/[^a-zA-Z0-9]+/", "", $auth->username); ?>";
        defaultDirectory = "<?php echo urlencode($startFolder); ?>";
        outputDirectory = "<?php echo CONVERT_FOLDER_RELATIVE; ?>";
        initialize();
    });
</script>
</body>
</html>
