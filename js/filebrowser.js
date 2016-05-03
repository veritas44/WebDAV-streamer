/**
 * Created by Koen on 25-4-2016.
 */
function urldecode(str) {
    return decodeURIComponent((str+'').replace(/\+/g, '%20'));
}

var currentDirectory;

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
});

function playVideo(file, name){
    var url = "get_video.php?file=" + file;

    $("#videoPlayer").attr("src", url);
    $("#videoTitle").html(urldecode(name));
}

function addToPlaylist(file, name){
    var url = "get_file.php?file=" + file;
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
    var file = currentDirectory + $("#playlistName").val() + ".pls";
    $("#loading").show();
    //alert(currentPath);
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            var response = xhttp.responseText;
            //alert(response);
            getDirectories(currentDirectory);


        }
        if(xhttp.readyState == 4){
            $("#loading").hide();
        }
    };
    xhttp.open("POST", "save_playlist.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("file=" + file + "&playlist=" + JSON.stringify(jPlaylist.playlist));
}