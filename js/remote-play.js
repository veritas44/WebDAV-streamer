/**
 * Created by Koen on 15-8-2016.
 */

//Currently not used:

function changeOutput(id) {
    if (typeof id === 'undefined') { id = sessionID; }


}
/*
function sendCommand(command, content) {
    if (typeof content === 'undefined') { content = {}; }

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            var response = xhttp.responseText;
        }
    };
    xhttp.open("POST", "api.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("command=" + command + "&content=" + encodeURIComponent(JSON.stringify(content)));
}

jquery_jplayer_1.bind(jQuery.jPlayer.event.play, function (event) {
    sendCommand("play", "");
});
jquery_jplayer_1.bind(jQuery.jPlayer.event.pause, function (event) {
    sendCommand("pause", "");
});

function setPlaylistTest(json) {
    jPlaylist = JSON.parse(decodeURIComponent(json));
}

function getPlaylistTest() {
    console.log(encodeURIComponent(JSON.stringify(jPlaylist)));
}
*/