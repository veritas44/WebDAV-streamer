//$(document).foundation();

var jquery_jplayer_1 = $('#jquery_jplayer_1');
var sessionID = null;
var sessionName = null;

function initialize() {
    try {
        if(localStorage.getItem(currentUser + "original") != "[]" && localStorage.getItem(currentUser + "playlist") != "[]") {
            jPlaylist.setPlaylist(JSON.parse(localStorage.getItem(currentUser + "original")));
            if(localStorage.getItem(currentUser + "current") < jPlaylist.playlist.length) {
                //alert(localStorage.getItem("current") + jPlaylist.original.length);
                //console.log(jPlaylist.playlist);
                //console.log(localStorage.getItem(currentUser + "current"));
                //console.log(jPlaylist.playlist.indexOf(JSON.parse(localStorage.getItem(currentUser + "current"))));
                jPlaylist.select(parseInt(localStorage.getItem(currentUser + "current")));
            }

            if(localStorage.getItem(currentUser + "shuffled") === "true"){
                jPlaylist.shuffle();
                jPlaylist._updateControls();
            }
            if(localStorage.getItem(currentUser + "looped") === "true"){
                console.log(jPlaylist.loop);
            }
        }
    } catch (Err){
        console.log(Err);
    }
    if(localStorage.getItem(currentUser + "directory") != null) {
        getDirectories(localStorage.getItem(currentUser + "directory"));
    } else {
        getDirectories(defaultDirectory);
    }

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

    if(localStorage.getItem(currentUser + "session") != null) {
        sessionID = localStorage.getItem(currentUser + "session");
        console.log("SessionID set: " + sessionID);
    } else {
        sessionID = Math.floor(100000 + Math.random() * 900000);
        if(setSessionID(sessionID)){
            //Hurray, it's set!
            console.log("SessionID set: " + sessionID);
        }
    }
    receiverID = sessionID;
    if(localStorage.getItem(currentUser + "sessionName") != null) {
        sessionName = localStorage.getItem(currentUser + "sessionName");
    } else {
        sessionName = sessionID;
    }
    updateSession();
}

$(window).unload(function() {
    localStorage.setItem(currentUser + "shuffled", jPlaylist.shuffled);
    localStorage.setItem(currentUser + "looped", jPlaylist.loop);
    localStorage.setItem(currentUser + "original", JSON.stringify(jPlaylist.original));
    localStorage.setItem(currentUser + "playlist", JSON.stringify(jPlaylist.playlist));
    localStorage.setItem(currentUser + "current", jPlaylist.original.indexOf(jPlaylist.playlist[jPlaylist.current]));
    localStorage.setItem(currentUser + "directory", currentDirectory);
});

function refreshTitle() {
    var current         = jPlaylist.current,
        playlist        = jPlaylist.playlist;
    jQuery.each(playlist, function (index, obj){
        if (index == current){
            var jsmediatags = window.jsmediatags;
            jsmediatags.Config.setXhrTimeoutInSec(0);
            jsmediatags.read(obj.mp3, {
                onSuccess: function(tag) {
                    var fancyName = (tag.tags.album ? tag.tags.album : "") +
                        (tag.tags.artist ? " - " + tag.tags.artist : "") +
                        (tag.tags.title ? " - " + tag.tags.title : obj.title);
                    $("#playInfo").html("<span onclick='refreshTitle()' title='Click to refresh'> "
                        + fancyName +
                        "</span>");
                    $("title").html((tag.tags.title ? tag.tags.title : obj.title) + " - WebDAV streamer");
                    jPlaylist.playlist[index].title = fancyName;
                    jPlaylist._refresh(true);
                    jPlaylist._highlight(jPlaylist.current);
                    //jPlaylist._highlight(index);
                },
                onError: function(error) {
                    console.log(error);
                    $("#playInfo").html("<span onclick='refreshTitle()' title='Click to refresh'>" + obj.title + "</span> ");
                    $("title").html(obj.title + " - WebDAV streamer");
                    jPlaylist._refresh(true);
                    jPlaylist._highlight(index);
                }
            });
        } // if condition end
    });
}

function setSessionID(id) {
    sessionID = id;
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            var response = xhttp.responseText;
            localStorage.setItem(currentUser + "session", sessionID);
            localStorage.setItem(currentUser + "sessionName", sessionID);
            return true;
        }
        if(xhttp.readyState == 4 && xhttp.status == 409){
            setSessionID(Math.floor(100000 + Math.random() * 900000));
        }
    };
    xhttp.open("POST", "session.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("action=announce&id=" + encodeURIComponent(sessionID) + "&name=" + encodeURIComponent(sessionID));

}

jquery_jplayer_1.bind(jQuery.jPlayer.event.play, function (event)
{
    refreshTitle();
    //console.log("YAY!");
    //if(jPlaylist.current < jPlaylist.length - 1) {
    /*
    try {
        $("#preloadAudio").attr("src", jPlaylist.playlist[jPlaylist.current + 1].mp3);
    } catch (err) {
        console.log(err);
    }
    */
        //console.log(jPlaylist.playlist[jPlaylist.current + 1].mp3);
    //}
});

jquery_jplayer_1.bind(jQuery.jPlayer.event.loadeddata, function (event)
{
    //console.log("YAY!");
    //if(jPlaylist.current < jPlaylist.length - 1) {
    try {
        $("#preloadAudio").attr("src", jPlaylist.playlist[jPlaylist.current + 1].mp3);
    } catch (err) {
        //console.log(err);
    }
    //console.log(jPlaylist.playlist[jPlaylist.current + 1].mp3);
    //}
});
/*
$("#jquery_jplayer_1").bind($.jPlayer.event.progress, function (event){
    // If media loading is complete
    if (event.jPlayer.status.seekPercent === 100){
        $(".jp-title .jp-title-loading").remove();

        // Otherwise, if media is still loading
    } else {
        $("#playInfo").html("Audio is loading...");
        if($(".jp-title .jp-title-loading").length == 0){
            $(".jp-title").prepend('<div class="jp-title-loading">Loading...</div>');
        }
    }
});

$("#jquery_jplayer_1").bind($.jPlayer.event.waitForLoad, function (event){
    // If media loading is complete
    if (event.jPlayer.status.seekPercent === 100){
        $(".jp-title .jp-title-loading").remove();

        // Otherwise, if media is still loading
    } else {
        $("#playInfo").html("Audio is loading...");
        if($(".jp-title .jp-title-loading").length == 0){
            $(".jp-title").prepend('<div class="jp-title-loading">Loading...</div>');
        }
    }
});
*/
/*
var ad = 1;

jquery_jplayer_1.bind($.jPlayer.event.ended, function (event)
{
    if(ad % 2 == 0) {
        try {
            jPlaylist.add({
                title: "Reclame",
                mp3: "http://berkleyandsanders.ml/radio_ad.mp3"
            });
            jPlaylist.playlist.move(jPlaylist.playlist.length - 1, jPlaylist.current + 1);
            //$("#preloadAudio").attr("src", jPlaylist.playlist[jPlaylist.current + 1].mp3);
        } catch (err) {
            console.log(err);
        }
    } else {
        jPlaylist._refresh(true);
        jPlaylist.remove(jPlaylist.current);
    }
    ad++;
    //console.log(jPlaylist.playlist[jPlaylist.current + 1].mp3);
    //}
});
*/

function checkLoaded() {
    var duration = jquery_jplayer_1.data().jPlayer.status.duration;
    var paused = jquery_jplayer_1.data().jPlayer.status.paused;
    if (duration == 0 && paused == false){
        $(".buffer-bar").show();
    } else {
        $(".buffer-bar").hide();
    }
}
setInterval(function(){
    //refreshTitle();
    $(".row.full-height").css("padding-bottom", $(".navbar-fixed-bottom").css('height'));
}, 2000);

function updateSession() {
    $.post( "session.php", { action: "update", id: sessionID, name: sessionName } );
}
setInterval(function() {
    updateSession();
},15000);
setInterval(function() {
    checkLoaded();
}, 1000);