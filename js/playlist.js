var jPlaylist;
$(document).ready(function(){
    jPlaylist = new jPlayerPlaylist({
        jPlayer: "#jquery_jplayer_1",
        cssSelectorAncestor: "#jp_container_1"
    }, [

    ], {
        playlistOptions: {
            autoPlay: true,
            enableRemoveControls: true
        },
        swfPath: "./jplayer/jquery.jplayer.swf",
        solution: "html,aurora,flash",
        supplied: "mp3,flac",
        wmode: "window",
        useStateClassSkin: true,
        autoBlur: false,
        smoothPlayBar: false,
        keyEnabled: true
    });

    //$("#jplayer_inspector_1").jPlayerInspector({jPlayer:$("#jquery_jplayer_1")});
});