<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 25-4-2016
 * Time: 00:00
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
    <link rel='shortcut icon' type='image/x-icon' href='favicon.ico' />
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css" href="css/dataTables.bootstrap.css">
    <link rel="stylesheet" type="text/css" href="jplayer/skin/foundation/css/jplayer.blue.monday.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
<nav id="navhead" class="navbar navbar-default navbar-fixed-top">
    <div class="container" style="height: 100%">
        <div id="navbar" class="nav-full" style="height: 100% !important;">
            <ul class="nav navbar-nav nav-full">
                <li class="nav-full" id="logo"><div class="nav-logo"><h4><img src="img/logo.svg" alt="Logo" style="height: 30px; width: auto;" > WebDAV streamer</h4></div></li>
                <li class="nav-full">
                    <div id="jquery_jplayer_1" class="jp-jplayer"></div>
                    <div id="jp_container_1" class="jp-audio" role="application" aria-label="media player">
                        <div class="jp-type-playlist">
                            <div class="jp-gui jp-interface">
                                <div class="jp-controls">
                                    <button class="jp-previous" role="button" tabindex="0">previous</button>
                                    <button class="jp-play" role="button" tabindex="0">play</button>
                                    <button class="jp-next" role="button" tabindex="0">next</button>
                                    <button class="jp-stop" role="button" tabindex="0">stop</button>
                                </div>
                                <div class="jp-progress">
                                    <div class="jp-seek-bar">
                                        <div class="jp-play-bar"></div>
                                    </div>
                                </div>
                                <div class="jp-volume-controls">
                                    <button class="jp-mute" role="button" tabindex="0">mute</button>
                                    <button class="jp-volume-max" role="button" tabindex="0">max volume</button>
                                    <div class="jp-volume-bar">
                                        <div class="jp-volume-bar-value"></div>
                                    </div>
                                </div>
                                <div class="jp-time-holder">
                                    <div class="jp-current-time" role="timer" aria-label="time">&nbsp;</div>
                                    <div class="jp-duration" role="timer" aria-label="duration">&nbsp;</div>
                                </div>
                                <div class="jp-toggles">
                                    <button class="jp-repeat" role="button" tabindex="0">repeat</button>
                                    <button class="jp-shuffle" role="button" tabindex="0">shuffle</button>
                                </div>
                            </div>

                            <div class="jp-no-solution">
                                <span>Update Required</span>
                                To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
                            </div>
                        </div>
                    </div>
                </li>
                <li class="nav-full">
                    <div class="nav-logo" id="playInfo"></div>
                </li>
            </ul>
            <ul class="nav navbar-nav navbar-right nav-full">
                <li class="nav-full"><a href="javascript:;" data-toggle="modal" data-target="#favouriteFiles">Favourite files</a></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Menu <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li class="nav-full"><a href="manage_users.php" target="_blank">Manage users</a></li>
                        <li class="nav-full"><a href="javascript:;" data-toggle="modal" data-target="#changePassword">Change password</a></li>
                        <li class="nav-full"><a href="login.php?logout=1">Log out</a></li>
                        <li class="nav-full"><a href="javascript:;" data-toggle="modal" data-target="#about">About</a></li>
                    </ul>
                <li class="nav-full"><img src="img/loading.gif" alt="Loading" id="loading" style="display: none;"></li>
                </li>
            </ul>
        </div>
        <!--
                <li><button class="button" id="previous">Previous</button> </li>
                <li><button class="button" id="play">Play</button><button class="button" id="pause">Pause</button> </li>
                <li><button class="button" id="next">Next</button> </li>
                <li><button class="button" id="stop">Stop</button> </li>
                <li><span id="currentTime"></span> / <span id="duration"></span></li>
                <li><button class="button" id="mute">Mute</button><button class="button" id="unmute">Unmute</button> </li>
                -->

    </div>
</nav>
<div class="row" id="content" style="margin: 0">
    <div class="col-lg-6" id="playlist">
        <div style="height: 10px;"></div>
        <div class="blog-post">
            <div class="col-md-3">
                <h3>Playlist</h3>
            </div>
            <div class="col-md-9 text-right form-inline" style="margin-top: 20px; margin-bottom: 10px;">
                <button class="btn blue" onclick="jPlaylist.shuffle(true, false);">Reshuffle</button>
                <button href="javascript:;" class="btn blue" data-toggle="modal" data-target="#savePlaylist">Save playlist</button>
                <button href="javascript:;" class="btn blue" onclick="jPlaylist.remove()">Clear playlist</button>
            </div>
            <table class="jp-playlist table table-striped table-hover" id="jp-playlist">

            </table>
        </div>
    </div>
    <div class="col-lg-6">
        <div style="height: 10px;"></div>
        <div class="blog-post" id="filebrowser">

        </div>
    </div>
</div>

<footer class="footer">
    <a href="#navhead" class="btn blue btn-footer">Player</a><a href="#filebrowser" class="btn blue btn-footer">File browser</a>
</footer>

<div class="modal fade" tabindex="-1" role="dialog" id="about">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">About</h4>
            </div>
            <div class="modal-body">
                <p>The story of a simple WebDAV streamer</p>
                <p>WebDAV streamer was created by Koenvh after searching for countless hours on the internet for a simple web audio player that supported WebDAV.<br>
                    He could not find one, so he decided to create one himself. <br>
                    Once he started programming WebDAV streamer, he quickly found out why...<br>
                    Still, he soldiered on, to give the world the piece of magnificence you are currently using.</p>
                <p class="text-center"><a href="http://koenvh.nl"><img src="img/koenvh_logo.png" alt="Koenvh"></a></p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="video">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="videoTitle">Video</h4>
            </div>
            <div style="height: 100%; width: 100%; background-color: black;">
                <div class="embed-responsive embed-responsive-16by9">
                    <video id="videoPlayer" class="embed-responsive-item" controls src='' autoplay></video>
                </div>
                <textarea id="videoProgress" class="shell" style="height: 100%; width: 100%;" readonly></textarea>
            </div>
        </div>
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
                    <button onclick="savePlaylist()" data-close class="btn btn-default blue">Save playlist</button>
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
                    <button class="btn blue" data-dismiss="modal" onclick="openPlaylist(playlistFile, playlistName, true)">Replace current playlist</button>
                    <button class="btn blue" data-dismiss="modal" onclick="openPlaylist(playlistFile, playlistName, false)">Add to current playlist</button>
                    <!--button class="btn" data-dismiss="modal">Cancel</button-->
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="favouriteFiles">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Favourite files:</h4>
            </div>

            <div class="modal-body">
                <table class="table table-striped table-hover" id="favouriteTable">
                    <tr><td>Nothing yet, add a favourite by clicking on the star</td></tr>
                </table>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="changePassword">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Change password:</h4>
            </div>

            <div class="modal-body">
                <form action="" method="post">
                    <?php
                    if($password_change_response != "none"){
                        echo "<div class='alert alert-warning'>$password_change_response</div>";
                    }
                    ?>
                    <div class="form-group">
                        <input type="password" placeholder="New password" name="newpass1" class="form-control input-sm">
                    </div>
                    <div class="form-group">
                        <input type="password" placeholder="New password (again)" name="newpass2" class="form-control input-sm">
                    </div>
                    <input type="submit" class="btn blue" value="Change password">
                </form>
            </div>

        </div>
    </div>
</div>

<script src="js/jquery-2.2.3.js"></script>
<script src="js/jquery.dataTables.js"></script>
<script src="js/dataTables.bootstrap.js"></script>
<script src="js/bootstrap.js"></script>
<script src="js/jsmediatags.js"></script>
<script src="js/RowSorter.js"></script>

<script src="jplayer/jquery.jplayer.min.js"></script>
<script src="jplayer/jplayer.playlist.js"></script>

<script src="js/playlist.js"></script>
<script src="js/filebrowser.js"></script>

<script src="index.js"></script>

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

