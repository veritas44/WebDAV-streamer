/**
 * Created by Koen on 15-8-2016.
 */

//Currently not used:

var receiverID = sessionID;
var masterID = "";
var isSlave = false;
var isMaster = false;
var isConnected = false;
var lastVolume = 0.8;

function changeOutput(id) {
    if (typeof id === 'undefined') { id = sessionID; }
    disconnectCommand();
    receiverID = id;
    connectCommand();
    //alert("Sure thing, did that for ya");
    getSessions();

}

function hideVolume() {
    isMaster = true;
    lastVolume
    $("#jquery_jplayer_1").jPlayer("volume", 0);
    setTimeout(function() {
        $(".jp-volume-max,.jp-mute,.jp-unmute,.jp-volume-bar").hide(1, function () {

        });
    }, 1000);
    $("#devicesButton").addClass("btn-success");
}

function showVolume() {
    isMaster = false;
    $(".jp-volume-max,.jp-mute,.jp-unmute,.jp-volume-bar").show(1, function () {
        $("#jquery_jplayer_1").jPlayer("volume", 0.8);
        isConnected = false;
        receiverID = sessionID;
    });
    $("#devicesButton").removeClass("btn-success");
}

function sendCommand(command, content, id) {
    if (typeof content === 'undefined') { content = {}; }
    if (typeof id === 'undefined') { id = receiverID; }

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            var response = xhttp.responseText;
        }
    };
    xhttp.open("POST", "api.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("command=" + command + "&content=" + encodeURIComponent(JSON.stringify(content)) + "&sender=" + sessionID +  "&receiver=" + id);
    console.log("command=" + command + "&content=" + encodeURIComponent(JSON.stringify(content)) + "&sender=" + sessionID +  "&receiver=" + id);
}

function connectCommand() {
    if(receiverID != sessionID) {
        hideVolume();
        sendCommand("connect", {
            sender: sessionID,
            receiver: receiverID
        });
        setTimeout(function () {
            if(!isConnected){
                disconnectCommand();
                alert("Client did not respond");
            }
        }, 10000);
    }
}

function disconnectCommand() {
    if(receiverID != sessionID){
        sendCommand("disconnect");
        showVolume();
    }
}

function disconnectMaster() {
    sendCommand('disconnectMaster', '', masterID);
    remotePlayer.pause();
    $('#remoteConnected').hide();
    isSlave = false;
}

function confirmConnection() {
    sendCommand('confirmConnection', '', masterID);
}

jquery_jplayer_1.bind(jQuery.jPlayer.event.play, function (event) {
    if(isMaster) {
        sendCommand("play", jPlaylist.playlist[jPlaylist.current]);
    }
});
jquery_jplayer_1.bind(jQuery.jPlayer.event.pause, function (event) {
    if(isMaster) {
        sendCommand("pause", "");
    }
});
setInterval(function() {
    if(isMaster) {
        var jPlayerData = $("#jquery_jplayer_1").data();
        sendCommand("timeupdate", {
            sender: sessionID,
            time: jPlayerData.jPlayer.status.currentTime,
            paused: jPlayerData.jPlayer.status.paused,
            mp3: jPlaylist.playlist[jPlaylist.current].mp3
        });
    }
}, 600);

var remotePlayer = document.createElement("AUDIO");
remotePlayer.volume = 1;

function receiveCommand() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            var response = JSON.parse(xhttp.responseText);
            for(var i = 0; i <= response.length; i++){
                var commandObject = response[i];
                //console.log(commandObject);
                if(typeof commandObject !== "undefined" && commandObject.receiver == sessionID){
                    var content = {};
                    if(commandObject.content != ""){ content = JSON.parse(commandObject.content)}
                    if(commandObject.command == "connect"){
                        isSlave = true;
                        $("#remoteConnected").show();
                        $("#jquery_jplayer_1").jPlayer("stop");
                        masterID = content.sender;
                        confirmConnection();
                    }
                    if(isSlave && commandObject.sender == masterID) {
                        switch (commandObject.command) {
                            case "play":
                                //console.log(content.mp3);
                                //jPlaylist.add(content, true);
                                if(remotePlayer.src != content.mp3){
                                    remotePlayer.src = content.mp3;
                                }
                                remotePlayer.play();
                                //playAudio(content.mp3, content.mp3);
                                break;
                            case "pause":
                                remotePlayer.pause();
                                break;
                            case "timeupdate":
                                if(remotePlayer.src != content.mp3){
                                    remotePlayer.src = content.mp3;
                                }
                                if(content.time > remotePlayer.currentTime + 7 || content.time < remotePlayer.currentTime - 7){
                                    remotePlayer.currentTime = content.time;
                                }
                                if(remotePlayer.paused && content.paused == false){
                                    remotePlayer.play();
                                }
                                break;
                            case "disconnect":
                                remotePlayer.pause();
                                $('#remoteConnected').hide();
                                isSlave = false;
                                break;
                        }
                        $.post( "api.php", { remove: commandObject.id } );
                    }
                    if(isMaster){
                        if(commandObject.command == "disconnectMaster"){
                            showVolume();
                            alert("The client disconnected.");
                        }
                        if(commandObject.command == "confirmConnection"){
                            isConnected = true;
                        }
                        $.post("api.php", {remove: commandObject.id});
                    }
                }
            }
        }
    };
    xhttp.open("POST", "api.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("");
}

setInterval(function() {
    receiveCommand();
}, 400);
