//$(document).foundation();
$(document).ready(function () {
    
    try {
        if(localStorage.getItem(currentUser + "original") != "[]" && localStorage.getItem(currentUser + "playlist") != "[]") {
            jPlaylist.setPlaylist(jQuery.parseJSON(localStorage.getItem(currentUser + "original")));
            jPlaylist.shuffled = Boolean(localStorage.getItem(currentUser + "shuffled"));
            if(jPlaylist.shuffled){
                jPlaylist.playlist = jQuery.parseJSON(localStorage.getItem(currentUser + "playlist"))
            }
            if(localStorage.getItem(currentUser + "current") < jPlaylist.original.length) {
                //alert(localStorage.getItem("current") + jPlaylist.original.length);
                jPlaylist.select(parseInt(localStorage.getItem(currentUser + "current")));
            }
        }
    } catch (Err){
        console.log(Err);
    }
    if(localStorage.getItem(currentUser + "directory") != null) {
        getDirectories(localStorage.getItem(currentUser + "directory"));
    } else {
        getDirectories("<?php echo urlencode($startFolder); ?>");
    }

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

$(window).unload(function() {
    localStorage.setItem(currentUser + "original", JSON.stringify(jPlaylist.original));
    localStorage.setItem(currentUser + "playlist", JSON.stringify(jPlaylist.playlist));
    localStorage.setItem(currentUser + "current", jPlaylist.current);
    localStorage.setItem(currentUser + "shuffled", jPlaylist.shuffled);
    localStorage.setItem(currentUser + "directory", currentDirectory);
});

function refreshTitle() {
    var current         = jPlaylist.current,
        playlist        = jPlaylist.playlist;
    jQuery.each(playlist, function (index, obj){
        if (index == current){
            var jsmediatags = window.jsmediatags;
            jsmediatags.read(obj.mp3, {
                onSuccess: function(tag) {
                    $("#playInfo").html("<div style='color: #666;'>"
                        + (tag.tags.title ? tag.tags.title : obj.title) +
                        (tag.tags.album ? " <br> " + tag.tags.album : "") +
                        (tag.tags.artist ? " <br> " + tag.tags.artist : "") +
                        "</div>");
                    $("title").html((tag.tags.title ? tag.tags.title : obj.title) + " - WebDAV streamer");
                },
                onError: function(error) {
                    console.log(error);
                    $("#playInfo").html("<div style='color: #666;'>" + obj.title + "</div>");
                    $("title").html(obj.title + " - WebDAV streamer");
                }
            });
        } // if condition end
    });
}

jQuery("#jquery_jplayer_1").bind(jQuery.jPlayer.event.play, function (event)
{
    refreshTitle();
});

jQuery('#video').bind('hidden.bs.modal', function (event) {
    console.log("Paused");
    document.getElementById("videoPlayer").pause();
});

setTimeout(refreshTitle(), 10000);