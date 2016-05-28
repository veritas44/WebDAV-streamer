/**
 * Created by Koen on 25-4-2016.
 */
function urldecode(str) {
    return decodeURIComponent((str+'').replace(/\+/g, '%20'));
}

var currentDirectory;
var supportedAudioMimeTypes;
var supportedVideoMimeTypes;
var playlistName;
var playlistFile;

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
    supportedAudioMimeTypes = determineSupportedAudio();
    supportedVideoMimeTypes = determineSupportedVideo();
});

function playVideo(file, name){
    var url = "get_video.php?file=" + file + "&support=" + encodeURIComponent(JSON.stringify(supportedVideoMimeTypes));
    console.log(url);
    $("#videoPlayer").attr("src", url);
    $("#videoTitle").html(urldecode(name));
    document.getElementById("videoPlayer").play();
}

function addToPlaylist(file, name){
    var url = "get_file.php?file=" + file + "&support=" + encodeURIComponent(JSON.stringify(supportedAudioMimeTypes));
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

function setPlaylist(file, name) {
    playlistName = name;
    playlistFile = file;
}

function openPlaylist(file, name, replace) {
    if (typeof replace === 'undefined') { replace = false; }
    $("#loading").show();
    //alert(currentPath);
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            var response = $.parseJSON(xhttp.responseText);
            //alert(response);
            if(replace == true){
                jPlaylist.remove();
            }
            for(var j = 0; j < response.length; j++){
                //alert(j);
                //alert(response[i][0]);
                addToPlaylist(response[j][0], response[j][1]);
            }
            if(replace == true){
                jPlaylist.select(0);
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

function determineSupportedVideo() {
    var mimeTypes = {
        "video/mp4": false,
        "video/ogg": false,
        "video/webm": false,
        "video/x-flv": false
    };
    var aud = document.createElement('video');
    for (var key in mimeTypes) {
        if (!mimeTypes.hasOwnProperty(key)) continue;
        if (aud.canPlayType(key) == "probably" || aud.canPlayType(key) == "maybe"){
            mimeTypes[key] = true;
        }
    }
    //console.log(mimeTypes);
    return mimeTypes;
}

function getFavourites() {
    var xhttp = new XMLHttpRequest();
    var returnData = "[]";
    xhttp.onreadystatechange = function() {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            //console.log(xhttp.responseText);
            returnData = xhttp.responseText;
            console.log("GETFAVOURITES: " + xhttp.responseText);
        } else {
            returnData = "[]";
        }
    };
    xhttp.open("POST", "favourites.php", false);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("action=open");
    return returnData;
}

function saveFavourites(favourites) {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            var response = xhttp.responseText;
            if(response == "success"){

            }
        }
    };
    xhttp.open("POST", "favourites.php", false);
    xhttp.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
    xhttp.send(JSON.stringify(favourites));
    console.log("SAVEFAVOURITES: " + JSON.stringify(favourites));
}

function openFavourite (file, name, type) {
    switch (type) {
        case "playlist":
            setPlaylist(file, name);
            $("#replacePlaylist").modal('show');
            break;
        case "video":
            playVideo(file, name);
            $("#video").modal('show');
            break;
        case "audio":
            addToPlaylist(file, name);
            jPlaylist.play(jPlaylist.playlist.length - 1);
            break;
    }
    $("#favouriteFiles").modal("hide");
}

function populateFavouriteFiles() {
    try {
        var favouriteFiles = jQuery.parseJSON(getFavourites());
        var tableContent = "";
        for(var i = 0; i < favouriteFiles.length; i++){
            //console.log(favouriteFiles[i].file);
            tableContent += "<tr>" +
                "<td class='sorter'></td>" +
                "<td><a href='javascript:;' onclick='openFavourite(\"" + (favouriteFiles[i].file) + "\",\"" + encodeURIComponent(favouriteFiles[i].name) + "\" ,\"" + favouriteFiles[i].type + "\")'>" + favouriteFiles[i].name + "</a></td>" +
                "<td align='right'><a href='javascript:;' onclick='removeFavourite($(this).closest(\"tr\").index())'><img src='img/icons/cross.png' alt='Del'></a></td>";
        }
        $("#favouriteTable").html(tableContent);
        $.rowSorter.destroy('#favouriteTable');
        $('#favouriteTable').rowSorter({
            handler: 'td.sorter',
            onDragStart: function(tbody, row, ind,ex)
            {
                //log('index: ' + index);
                //console.log('onDragStart: active row\'s index is ' + index);
            },
            onDrop: function(tbody, row, new_index, old_index)
            {
                //log('old_index: ' + old_index + ', new_index: ' + new_index);
                //console.log('onDrop: row moved from ' + old_index + ' to ' + new_index);
                favouriteFiles.move(old_index, new_index);
                saveFavourites(favouriteFiles);
            }
        });
    } catch (e) {
        console.log(e);
    }
}

function addFavourite(file, name, type) {
    var favouriteFiles;
    try {
         favouriteFiles = jQuery.parseJSON(getFavourites());
    } catch (e) {
        console.log(e);
        favouriteFiles = [];
    }
    if(favouriteFiles == null){
        favouriteFiles = [];
    }
    favouriteFiles.push(
        {
            file: file,
            name: urldecode(name),
            type: type
        });
    //console.log(favouriteFiles);
    saveFavourites(favouriteFiles);
    console.log("ADDFAVOURITE: " + favouriteFiles);
    populateFavouriteFiles();
}

function removeFavourite(index) {
    try {
        var favouriteFiles = jQuery.parseJSON(getFavourites());
        favouriteFiles.splice(index, 1);
        saveFavourites(favouriteFiles);
        populateFavouriteFiles();
    } catch (e) {
        console.log(e);
    }
}