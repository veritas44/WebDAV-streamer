<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 25-4-2016
 * Time: 00:00
 */

require_once ("includes.php");

$requestURL = 'Music/GTR2/24hrs.mp3';
$requestURL = 'Music/Alistair Griffin - Just Drive.mp3';
//$requestURL = 'Music/Monstercat/Monstercat 024/Monstercat - Monstercat 024 - 32 Presage (Album Mix).flac';

$requestURL = str_replace(' ', '%20', $requestURL);
//$response = $client->request('GET', $requestURL);
//$response = $client->request('HEAD', $requestURL);

?>
<!DOCTYPE html>
<html>
<head>
    <title>WebDAV streamer</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" type="text/css" href="css/foundation.min.css">
    <link rel="stylesheet" type="text/css" href="jplayer/skin/foundation/css/jplayer.blue.monday.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <nav class="top-bar" data-topbar role="navigation" data-options="sticky_on: large">
        <div class="top-bar-left">
            <ul class="menu">
                <li class="menu-text">WebDAV streamer</li>
                <li class="menu-text">
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
                <!--
                <li><button class="button" id="previous">Previous</button> </li>
                <li><button class="button" id="play">Play</button><button class="button" id="pause">Pause</button> </li>
                <li><button class="button" id="next">Next</button> </li>
                <li><button class="button" id="stop">Stop</button> </li>
                <li><span id="currentTime"></span> / <span id="duration"></span></li>
                <li><button class="button" id="mute">Mute</button><button class="button" id="unmute">Unmute</button> </li>
                -->
            </ul>

        </div>
        <div class="top-bar-right">
            <ul class="menu">
                <li><a data-open="about">About</a></li>
                <li><img src="img/loading.gif" alt="Loading" id="loading" style="display: none;"></li>
            </ul>
            <!--
            <li><a href='#' class="button" onclick="addAllToPlaylist()">Add all to playlist</a></li>
            <li><a href="#" class="button" onclick="jPlaylist.remove()">Clear playlist</a></li>
            -->
            <!--audio controls id="player" src="file.mp3"></audio-->

        </div>
    </nav>
    <div class="row" id="content">
        <div class="medium-6 columns" id="playlist">
            <div style="height: 10px;"></div>
            <div class="blog-post">
                <h3>Playlist</h3>
                <table class="jp-playlist" id="jp-playlist">

                </table>
                <!--table>
                <thead>
                <tr>
                    <th>Songs</th>
                    <th></th>
                </tr>
                </thead>
                <tbody id="playlist">

                </tbody>
            </table-->
            </div>
        </div>
        <div class="medium-6 columns">
            <div style="height: 10px;"></div>
            <div class="blog-post" id="filebrowser">

            </div>
        </div>
    </div>
    <div class="reveal" id="about" data-reveal>
        <h1>About</h1>
        <p class="lead">The story of a simple WebDAV streamer</p>
        <p>WebDAV streamer was created by Koenvh after searching for countless hours on the internet for a simple web audio player that supported WebDAV.<br>
            He could not find one, so he decided to create one himself. <br>
        Once he started programming WebDAV streamer, he quickly found out why...<br>
        Still, he soldiered on, to give the world the piece of magnificence you are currently using.</p>
        <p class="text-center"><a href="http://koenvh.nl"><img src="img/koenvh_logo.png" alt="Koenvh"></a></p>
        <button class="close-button" data-close aria-label="Close reveal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <script src="js/codecs/aurora.js"></script>
    <script src="js/codecs/flac.js"></script>

    <script src="js/jquery-2.2.3.js"></script>
    <script src="js/foundation.min.js"></script>

    <script src="jplayer/jquery.jplayer.min.js"></script>
    <script src="jplayer/jplayer.playlist.js"></script>

    <script src="js/playlist.js"></script>
    <script src="js/filebrowser.js"></script>


    <script>
        $(document).foundation();
        $(document).ready(function () {
            getDirectories("<?php echo urlencode($startFolder); ?>");

            $("#loading").show();
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (xhttp.readyState == 4 && xhttp.status == 200) {
                    var response = xhttp.responseText;
                    //alert(response);
                }
                if(xhttp.readyState == 4){
                    $("#loading").hide();
                }
            };
            xhttp.open("GET", "remove_old_files.php", true);
            xhttp.send();
        });
    </script>
</body>
</html>
