//$(document).foundation();
function initialize() {
    $("#loading").show();
    try {
        if(localStorage.getItem(currentUser + "original") != "[]" && localStorage.getItem(currentUser + "playlist") != "[]") {
            jPlaylist.setPlaylist(jQuery.parseJSON(localStorage.getItem(currentUser + "original")));
            if(localStorage.getItem(currentUser + "current") < jPlaylist.playlist.length) {
                //alert(localStorage.getItem("current") + jPlaylist.original.length);
                //console.log(jPlaylist.playlist);
                //console.log(localStorage.getItem(currentUser + "current"));
                //console.log(jPlaylist.playlist.indexOf(jQuery.parseJSON(localStorage.getItem(currentUser + "current"))));
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

    populateFavouriteFiles();

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

    checkHeaderHeight();
}

function initSorting() {
    $('#jp-playlist').rowSorter({
        handler: 'td.sorter',
        onDragStart: function(tbody, row, index)
        {
            //log('index: ' + index);
            //console.log('onDragStart: active row\'s index is ' + index);
        },
        onDrop: function(tbody, row, new_index, old_index)
        {
            //log('old_index: ' + old_index + ', new_index: ' + new_index);
            //console.log('onDrop: row moved from ' + old_index + ' to ' + new_index);
            jPlaylist.playlist.move(old_index, new_index);
            if(jPlaylist.shuffled == false) {
                jPlaylist.original.move(old_index, new_index);
            }
            if(jPlaylist.current == old_index){
                jPlaylist.current = new_index;
            }
        }
    });

    return "Done";
}

$(window).unload(function() {
    localStorage.setItem(currentUser + "shuffled", jPlaylist.shuffled);
    localStorage.setItem(currentUser + "looped", jPlaylist.loop);
    localStorage.setItem(currentUser + "original", JSON.stringify(jPlaylist.original));
    localStorage.setItem(currentUser + "playlist", JSON.stringify(jPlaylist.playlist));
    localStorage.setItem(currentUser + "current", jPlaylist.original.indexOf(jPlaylist.playlist[jPlaylist.current]));
    localStorage.setItem(currentUser + "directory", currentDirectory);
});

function checkHeaderHeight(){
    var header = $("#navhead");
    var content = $("#content");

    //console.log(header.css("height"));
    if($(window).width() > 768) {
        content.css("top", header.css("height"));
    } else {
        content.css("top", 0);
    }
}

$(window).on('resize', function(){
    checkHeaderHeight();
});

function refreshTitle() {
    var current         = jPlaylist.current,
        playlist        = jPlaylist.playlist;
    jQuery.each(playlist, function (index, obj){
        if (index == current){
            var jsmediatags = window.jsmediatags;
            jsmediatags.read(obj.mp3, {
                onSuccess: function(tag) {
                    $("#playInfo").html("<div style='color: #666;' onclick='refreshTitle()' title='Click to refresh'>"
                        + (tag.tags.title ? tag.tags.title : obj.title) +
                        (tag.tags.album ? " <br> " + tag.tags.album : "") +
                        (tag.tags.artist ? " <br> " + tag.tags.artist : "") +
                        "</div>");
                    $("title").html((tag.tags.title ? tag.tags.title : obj.title) + " - WebDAV streamer");
                    clearInterval(titleInterval);
                    checkHeaderHeight();
                },
                onError: function(error) {
                    console.log(error);
                    $("#playInfo").html("<div style='color: #666;' onclick='refreshTitle()' title='Click to refresh'>" + obj.title + "</div>");
                    $("title").html(obj.title + " - WebDAV streamer");
                    titleInterval = setInterval("refreshTitle", 10000);
                    checkHeaderHeight();
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

var titleInterval = setInterval("refreshTitle", 10000);
setInterval(function(){$.post('refresh_session.php');},120000);