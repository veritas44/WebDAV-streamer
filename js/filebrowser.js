/**
 * Created by Koen on 25-4-2016.
 */
function urldecode(str) {
    return decodeURIComponent((str+'').replace(/\+/g, '%20'));
}

var currentDirectory;
var supportedMimeTypes;

function getDirectories(currentPath) {
    //alert(decodeURIComponent(currentPath));
    $("#loading").show();
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            var response = xhttp.responseText;
            //alert(response);
            $("#filebrowser").html(response);
            currentDirectory = currentPath;
        }
        if(xhttp.readyState == 4){
            $("#loading").hide();
        }
    };
    xhttp.open("GET", "get_folder.php?folder=" + currentPath, true);
    xhttp.send();
}

var playlistArray;

$(document).ready(function () {
   playlistArray = [];
    supportedMimeTypes = determineSupportedAudio();
});

function playVideo(file, name){
    var url = "get_video.php?file=" + file;

    $("#videoPlayer").attr("src", url);
    $("#videoTitle").html(urldecode(name));
}

function addToPlaylist(file, name){
    var url = "get_file.php?file=" + file + "&support=" + encodeURIComponent(JSON.stringify(supportedMimeTypes));
    console.log(url);
    jPlaylist.add({
        title: urldecode(name),
        mp3: url
    });

    //playlistArray.push(url);
    //playlistNamesArray.push(urldecode(name));

    /*
    var audio = document.getElementById("player");
    if(audio.paused){
        setPlayerSource(url);
    }
    */
}

function addAllToPlaylist(currentPath) {
    if (typeof currentPath === 'undefined') { currentPath = currentDirectory; }
    $("#loading").show();
    //alert(currentPath);
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            var response = $.parseJSON(xhttp.responseText);
            //alert(response);


            for(var j = 0; j < response.length; j++){
                //alert(j);
                //alert(response[i][0]);
                addToPlaylist(response[j][0], response[j][1]);
            }
        }
        if(xhttp.readyState == 4){
            $("#loading").hide();
        }
    };
    xhttp.open("GET", "get_all_files.php?folder=" + currentPath, true);
    xhttp.send();
}

function openPlaylist(file, name) {
    $("#loading").show();
    //alert(currentPath);
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            var response = $.parseJSON(xhttp.responseText);
            //alert(response);


            for(var j = 0; j < response.length; j++){
                //alert(j);
                //alert(response[i][0]);
                addToPlaylist(response[j][0], response[j][1]);
            }
        }
        if(xhttp.readyState == 4){
            $("#loading").hide();
        }
    };
    xhttp.open("GET", "get_playlist.php?file=" + file, true);
    xhttp.send();
}

function savePlaylist() {
    var purifiedPlaylist = [];
    jPlaylist.playlist.forEach(function (entry) {
        entry.mp3 = entry.mp3.split("&support=")[0];
        purifiedPlaylist[purifiedPlaylist.length] = entry;
    });

    var type = $("#playlistType").val();
    var file = currentDirectory + $("#playlistName").val() + "." + type;
    $("#playlistName").val("");
    $("#loading").show();
    //alert(currentPath);
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            var response = xhttp.responseText;
            alert(response);
            getDirectories(currentDirectory);
        }
        if(xhttp.readyState == 4){
            $("#loading").hide();
        }
    };
    xhttp.open("POST", "save_playlist.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("type=" + type + "&file=" + encodeURIComponent(file) + "&playlist=" + encodeURIComponent(JSON.stringify(purifiedPlaylist)));
    //console.log(JSON.stringify(jPlaylist.playlist));
}

function removeFile(file) {
    var r = confirm("Are you sure you want to delete this file?");
    if (r == true) {
        $("#loading").show();
        //alert(currentPath);
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
            if (xhttp.readyState == 4 && xhttp.status == 200) {
                var response = xhttp.responseText;
                //alert(response);
                getDirectories(currentDirectory);


            }
            if (xhttp.readyState == 4) {
                $("#loading").hide();
            }
        };
        xhttp.open("get", "remove_file.php?file=" + file, true);
        xhttp.send();
    }
}

function determineSupportedAudio() {
    var mimeTypes = {
        "audio/mpeg": false,
        "audio/x-mpeg": false,
        "audio/ogg": false,
        "audio/x-vorbis+ogg": false,
        "audio/webm": false,
        "audio/wav": false,
        "audio/x-wav": false,
        "audio/aac": false,
        "audio/flac": false
    };
    var aud = document.createElement('audio');
    for (var key in mimeTypes) {
        if (!mimeTypes.hasOwnProperty(key)) continue;
        if (aud.canPlayType(key) == "probably" || aud.canPlayType(key) == "maybe"){
            mimeTypes[key] = true;
        }
    }
    //console.log(mimeTypes);
    return mimeTypes;
}