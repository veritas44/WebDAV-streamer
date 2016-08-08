/**
 * Created by Koen on 25-4-2016.
 */
function urldecode(str) {
    try {
        return decodeURIComponent((str + '').replace(/\+/g, '%20'));
    } catch (err) {
        console.log(err);
        console.log(str);
        return str;
    }
}

var currentDirectory;
var supportedAudioMimeTypes;
var supportedVideoMimeTypes;
var playlistName;
var playlistFile;

function getDirectories(currentPath) {
    //alert(decodeURIComponent(currentPath));
    showLoader();
    hideVideo();
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            var response = xhttp.responseText;
            //alert(response);
            $("#content").html(response);
            currentDirectory = currentPath;
            hideLoader();
        }
        if(xhttp.readyState == 4){
            hideLoader();
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

function refreshVideoProgress(url) {
    try {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
            if (xhttp.readyState == 4 && xhttp.status == 200) {
                var videoProgress = $("#videoProgress");
                var response = xhttp.responseText;
                videoProgress.html(response);
                if (videoProgress.length) {
                    videoProgress.scrollTop(videoProgress[0].scrollHeight - videoProgress.height());
                }
            }
        };
        xhttp.open("GET", url, true);
        xhttp.send();
    } catch (err) {
        console.log(err);
    }
    console.log("Refreshed");
}

function playVideo(file, name){
    showLoader();

    var url = "get_video.php?file=" + file + "&support=" + encodeURIComponent(JSON.stringify(supportedVideoMimeTypes));
    var progress = outputDirectory + "/progress.txt";
    //var progress = "output/" + $.md5(currentUser + file) + ".progress";

    var videoPlayer = $("#videoPlayer");
    var videoProgress = $("#videoProgress");

    //refreshVideoProgress(progress);
    var videoProgressTimeout = setInterval("refreshVideoProgress('" + progress + "')", 2000);
    videoProgress.show();
    //videoPlayer.hide();

    videoPlayer.on("canplay", function () {
        //alert("Loaded!");
        videoProgress.hide();
        //videoPlayer.show();
        clearInterval(videoProgressTimeout);
    });

    console.log(url);

    videoPlayer.attr("src", url);
    hideLoader();
    showVideo();
    document.getElementById("videoPlayer").play();
}



function addToPlaylist(file, name){
    var url = "get_file.php?file=" + file + "&support=" + encodeURIComponent(JSON.stringify(supportedAudioMimeTypes));
    console.log(url);
    jPlaylist.add({
        title: urldecode(name),
        mp3: url
    });
    var added = $("#added");
    added.fadeIn("fast", function() {
        added.fadeOut("slow");
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

function youtube_parser(url){
    var regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/;
    var match = url.match(regExp);
    return (match&&match[7].length==11)? match[7] : false;
}

function addYouTube(id) {
    var YouTubeInMP3 = "https://www.youtubeinmp3.com/fetch/?format=JSON&video=http://www.youtube.com/watch?v=" + id;

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            try {
                var jsonResponse = JSON.parse(xhttp.responseText);
                addToPlaylist(encodeURIComponent(jsonResponse.link), encodeURIComponent(jsonResponse.title));
            } catch (err) {
                var youtubeIframe =  $('#youtubeIframe');
                youtubeIframe.contents().find('html').html(xhttp.responseText);
                addToPlaylist(encodeURIComponent("https://www.youtubeinmp3.com" + youtubeIframe.contents().find("#download").attr("href")), encodeURIComponent(youtubeIframe.contents().find("#videoTitle").text()));

                //alert("Could not add the YouTube video to the playlist!\n\n" + err);
            }
            /*
            jPlaylist.add({
                title: jsonResponse.title,
                mp3: jsonResponse.link
            });
            var added = $("#added");
            added.fadeIn("fast", function() {
                added.fadeOut("slow");
            });
            */
        } else if(xhttp.readyState == 4){
            alert("Could not add the YouTube video to the playlist!");
        }
    };
    xhttp.open("GET", YouTubeInMP3, true);
    xhttp.send();
}

function addAllToPlaylist(currentPath, type) {
    if (typeof currentPath === 'undefined') { currentPath = currentDirectory; }
    if (typeof type === 'undefined') { type = "folder"; }
    showLoader();
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
            hideLoader();
        }
        if(xhttp.readyState == 4){
            hideLoader();
        }
    };
    xhttp.open("GET", "get_all_files.php?" + type + "=" + currentPath, true);
    xhttp.send();
}

function setPlaylist(file, name) {
    playlistName = name;
    playlistFile = file;
}

function openPlaylist(file, name, replace) {
    if (typeof replace === 'undefined') { replace = false; }
    showLoader();
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
                setTimeout(function() {jPlaylist.select(0); }, 2000);
            }
            hideLoader();
        }
        if(xhttp.readyState == 4){
            hideLoader();
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
    showLoader();
    //alert(currentPath);
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            var response = xhttp.responseText;
            alert(response);
            $("#savePlaylist").modal("hide");
            getDirectories(currentDirectory);
            hideLoader();
        }
        if(xhttp.readyState == 4){
            hideLoader();
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
        showLoader();
        //alert(currentPath);
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
            if (xhttp.readyState == 4 && xhttp.status == 200) {
                var response = xhttp.responseText;
                alert(response);
                getDirectories(currentDirectory);
                hideLoader();
            }
            if (xhttp.readyState == 4) {
                hideLoader();
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
    xhttp.open("POST", "favourites.php?action=open", false);
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
    xhttp.open("POST", "favourites.php?action=update", false);
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

function addFavourite(file, name, type) {
    var favouriteFiles;
    try {
         favouriteFiles = JSON.parse(getFavourites());
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
}

function removeFavourite(index) {
    try {
        var favouriteFiles = JSON.parse(getFavourites());
        favouriteFiles.splice(index, 1);
        saveFavourites(favouriteFiles);
        loadPage("favourites.php");
    } catch (e) {
        console.log(e);
    }
}


function crawlLibrary(url) {
    var niceUrl = url.replace("refresh_library.php?folder=", "");
    niceUrl = niceUrl.replace("&overwrite=0", "");
    niceUrl = niceUrl.replace("&overwrite=1", "");
    niceUrl = decodeURIComponent(niceUrl);

    refreshArray.push(niceUrl);
    refreshCount++;
    refreshCurrentProcesses();
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            var response = xhttp.responseText;
            //console.log(response);
            //$(response).appendTo($("#searchTable")).slideDown("fast");
            $("#output").prepend(niceUrl + "<br>\n" + response + "\n============================<br>\n");
            var i = refreshArray.indexOf(niceUrl);
            if(i != -1) {
                refreshArray.splice(i, 1);
            }
            refreshDone++;
            refreshCurrentProcesses();
        } else if (xhttp.readyState == 4) {
            $("#output").prepend(niceUrl + "<br>\n" + "Something went wrong" + xhttp.status + "\n============================<br>\n");
            var i = refreshArray.indexOf(niceUrl);
            if(i != -1) {
                refreshArray.splice(i, 1);
            }
            refreshDone++;
            refreshCurrentProcesses();
        }
    };
    xhttp.open("get", url, true);
    xhttp.send();
}

function startRefresh(){
    refreshArray = [];
    refreshCount = 0;
    refreshDone = 0;
    var checked = 0;
    if($("#refreshOverwrite").is(':checked')){
        checked = 1;
    }
    var folder = encodeURIComponent($("#refreshFolder").val());
    crawlLibrary("refresh_library.php?folder=" + folder + "&overwrite=" +  checked);
    crawlLibrary("refresh_library.php?folder=remove");
    $("#output").prepend("<br>Start crawling... Keep this window open<br>\nIt might take up to 30 minutes to see any progress!");
}