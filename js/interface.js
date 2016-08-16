/**
 * Created by Koen on 25-6-2016.
 */
$(document).ready(function() {
    //Events:
    $(".show-playlist").click(function () {
        /*
            var content = $("#content");
            var playlist = $(".playlist-container");

            console.log(content.css("width"));

            if (playlist.is(":visible")){
                playlist.hide();
                content.animate({width: "85%"});
                playlist.animate({width: "0"});
            } else {
                playlist.show();
                content.animate({width: "45%"});
                playlist.animate({width: "40%"});
            }
        */
        $(".playlist-container").toggle({
            duration: "slow",
            step: function(now, tween) {
                $(".row.full-height").css("padding-bottom", $(".navbar-fixed-bottom").css('height'));
            },
            complete: function () {
                //Finished
                //$(".row.full-height").animate({"padding-bottom":$(".navbar-fixed-bottom").css('height')}, 500);
            }});
        $(".playlist-controls").toggle("slow", function () {
            //Finished
        });
    });
    $(".navbar-nav li a").click(function(event) {
        $(".navbar-collapse").collapse('hide');
    });
    $('.menu-left li a').click(function(e) {

        $('.menu-left li').removeClass('active');

        var $parent = $(this).parent();
        if (!$parent.hasClass('active')) {
            $parent.addClass('active');
        }
        e.preventDefault();
    });
    $(document).on("keydown", "#filesearch", function(e) {
        if (e.which == 13) {
            initialSearch(searchFolder);
            return false;
        }
    });
    $(document).on("keydown", "#dbsearch", function(e) {
        if (e.which == 13) {
            initialDbSearch();
            return false;
        }
    });
    $(document).on("keydown", "#albumsearch", function(e) {
        if (e.which == 13) {
            loadPage('albums.php?search=' + $('#albumsearch').val());
            return false;
        }
    });
    $(document).on("keydown", "#artistsearch", function(e) {
        if (e.which == 13) {
            loadPage('artists.php?search=' + $('#artistsearch').val());
            return false;
        }
    });
    $(document).on("keydown", "#genresearch", function(e) {
        if (e.which == 13) {
            loadPage('genres.php?search=' + $('#genresearch').val());
            return false;
        }
    });

    //Set on document load:
    $(".playlist-container").hide();
    $(".playlist-controls").hide();
    $(".buffer-bar").hide();
});

function loadPage2(file) {
    showLoader();
    hideVideo();
    //alert(currentPath);
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            $("#content").html(xhttp.responseText);
            $('.nav-collapse').toggle();
            hideLoader();
        }
        if (xhttp.readyState == 4) {
            hideLoader();
        }
    };
    xhttp.open("get", file, true);
    xhttp.send();
    console.log(file);
}

function loadPage(file) {
    $.when(loadPage2(file)).done(function (a1){
        return true;
    });
}

function showVideo() {
    $("#video").show();
    $("#content").hide();
    jPlaylist.pause();
}

function hideVideo() {
    $("#video").hide();
    $("#content").show();
    document.getElementById("videoPlayer").pause();
}

function showLoader() {
    var content = $("#content");
    //content.scrollTop(0);
    //content.css("overflow-y", "hidden");
    content.append('<div class="loader-background"><div class="loader">Loading&#8230;</div></div>');
    $('.loader-background').css('height', content.css('height'));
}

function hideLoader() {
    $("#content").css("overflow-y", "auto");
    $( ".loader-background" ).remove();
}

function search(url){
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            var response = xhttp.responseText;
            //console.log(response);
            //$(response).appendTo($("#searchTable")).slideDown("fast");
            $("#searchTable tbody").append(response);
        }
        if (xhttp.readyState == 4) {

        }
    };
    xhttp.open("get", url, true);
    xhttp.send();
}

function dbSearch(url){
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            var response = xhttp.responseText;
            //console.log(response);
            //$(response).appendTo($("#searchTable")).slideDown("fast");
            $("#databaseTable tbody").append(response);
        }
        if (xhttp.readyState == 4) {

        }
    };
    xhttp.open("get", url, true);
    xhttp.send();
}

function initialSearch(folder) {
    $("#searchTable tbody").empty();
    var filesearch = encodeURIComponent($("#filesearch").val());
    search("search.php?folder=" + folder + "&search=" + filesearch);
    $("#searchLoader").show();
}

function initialDbSearch() {
    $("#databaseTable tbody").empty();
    var filesearch = encodeURIComponent($("#dbsearch").val());
    dbSearch("db_search.php?search=" + filesearch);
}

function initialDbAdvancedSearch() {
    $("#databaseTable tbody").empty();
    var album = encodeURIComponent($("#asearchAlbum").val());
    var artist = encodeURIComponent($("#asearchArtist").val());
    var composer = encodeURIComponent($("#asearchComposer").val());
    var genre = encodeURIComponent($("#asearchGenre").val());
    var title= encodeURIComponent($("#asearchTitle").val());
    var year = encodeURIComponent($("#asearchYear").val());
    //console.log("db_search.php?search=1&album=" + album + "&artist=" + artist + "&composer=" + composer + "&genre=" + genre + "&title=" + title + "&year=" + year);
    dbSearch("db_search.php?search=1&album=" + album + "&artist=" + artist + "&composer=" + composer + "&genre=" + genre + "&title=" + title + "&year=" + year);
}

$(document).ajaxStop(function() {
    $("#searchLoader").hide();
});

function openAlbum(url) {
    /*
    $("[id^=album]").hide();
    $("#album" + id).show();
    */

    $("#albumModalContent").html("<div class='loader'></div>");
    $("#albumModal").modal('show');

    url = "albums.php?album=" + url;

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            var response = xhttp.responseText;
            $("#albumModalContent").html(response);
        }
        if (xhttp.readyState == 4) {

        }
    };
    xhttp.open("get", url, true);
    xhttp.send();
}

function openArtist(url) {

    $("#artistSongList").html("<div class='loader'></div>");

    url = "artists.php?artist=" + url;

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            var response = xhttp.responseText;
            $("#artistSongList").html(response);
            $("#content").animate({ scrollTop: 0 }, "fast");
            $("html,body").animate({ scrollTop: 0 }, "slow");
        }
        if (xhttp.readyState == 4) {

        }
    };
    xhttp.open("get", url, true);
    xhttp.send();
}

function openGenre(url) {

    $("#genreSongList").html("<div class='loader'></div>");

    url = "genres.php?genre=" + url;

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            var response = xhttp.responseText;
            $("#genreSongList").html(response);
            $("#content").animate({ scrollTop: 0 }, "fast");
            $("html,body").animate({ scrollTop: 0 }, "slow");
        }
        if (xhttp.readyState == 4) {

        }
    };
    xhttp.open("get", url, true);
    xhttp.send();
}

var refreshCount = 0;
var refreshDone = 0;
var refreshArray = [];

function refreshCurrentProcesses(){
    var str = "Currently running processes: (" + refreshArray.length + ") <br>\n" + refreshArray.join("<br>\n");
    var refreshProgress = "";
    if(refreshArray.length > 0) {
        refreshProgress = "<div class='progress'><div class='progress-bar progress-bar-striped active' role='progressbar' style='width:" + (refreshDone / refreshCount) * 100 + "%'>" + refreshDone + "/" + refreshCount + "</div></div>";
    } else {
        refreshProgress = "";
    }
    $("#refreshCount").html(str);
    $("#refreshProgress").html(refreshProgress);
}

function getSessions() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            var response = xhttp.responseText;
            $("#devicesContent").html(response);
        }
        if(xhttp.readyState == 4){

        }
    };
    xhttp.open("POST", "session.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("action=get");
}