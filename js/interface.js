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
            search();
            return false;
        }
    });

    //Set on document load:
    $(".playlist-container").hide();
    $(".playlist-controls").hide();
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
}

function hideLoader() {
    $("#content").css("overflow-y", "auto");
    $( ".loader-background" ).remove();
}
