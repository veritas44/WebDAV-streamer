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

        $(".playlist-container").toggle("slow", function () {
            //Finished
        });
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

function loadPage(file) {
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
    xhttp.open("get", file, false);
    xhttp.send();
    console.log(file);
}

function showVideo() {
    $("#video").show();
    $("#content").hide();
}

function hideVideo() {
    $("#video").hide();
    $("#content").show();
    document.getElementById("videoPlayer").pause();
}

function showLoader() {
    $("#content").append('<div class="loader-background"><div class="loader">Loading&#8230;</div></div>');
}

function hideLoader() {
    $( ".loader-background" ).remove();
}
