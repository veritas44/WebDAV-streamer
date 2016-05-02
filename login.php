<?php
session_start();
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 2-5-2016
 * Time: 22:49
 */
require_once ("config.php");
require_once ("auth.php");

$message = "";

if(isset($_GET["logout"])){
    session_destroy();
}

$auth = new Auth($users);

if(key_exists("autologin", $auth->users)){
    $_SESSION["username"] = "autologin";
    $_SESSION["password"] = $auth->users["autologin"]["password_streamer"];
    header("Location: index.php");
    die();
}

if(isset($_POST["username"]) && isset($_POST["password"])){
    if($auth->login($_POST["username"], $_POST["password"]) == "success"){
        $_SESSION["username"] = $_POST["username"];
        $_SESSION["password"] = $_POST["password"];
        header("Location: index.php");
        die();
    } else {
        $message = "Wrong credentials";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>WebDAV streamer</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel='shortcut icon' type='image/x-icon' href='favicon.ico' />
    <link rel="stylesheet" type="text/css" href="css/foundation.min.css">
    <link rel="stylesheet" type="text/css" href="jplayer/skin/foundation/css/jplayer.blue.monday.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">

    <style>
        body {
            height: 100%;
            overflow: hidden;
            background: #e6e6e6;
        }
        .login-box {
            background: #fff;
            border: 1px solid #ddd;
            margin: 100px 0;
            padding: 40px 20px 20px 20px;
        }
    </style>
</head>
<body>
<!-- Source: http://codepen.io/johngerome/pen/pdrgk -->
<div class="large-3 large-centered columns">
    <div class="login-box">
        <div class="row">
            <div class="large-12 columns">
                <div class="row">
                    <div class="large-12 columns">
                        <h4><img src="img/logo.svg" alt="Logo" style="height: 35px; width: auto;"> WebDAV streamer</h4>
                    </div>
                </div>
                <?php
                if($message != "") {
                    echo '<div class="callout warning">';
                    echo '<p>' . $message . '</p>';
                    echo '</div>';
                }
                ?>
                <form method="post" action="login.php">
                    <div class="row">
                        <div class="large-12 columns">
                            <input type="text" name="username" placeholder="Username" value="<?php echo (isset($_POST["username"]) ? $_POST["username"] : ""); ?>"/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="large-12 columns">
                            <input type="password" name="password" placeholder="Password" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="large-12 large-centered columns">
                            <input type="submit" class="expanded button" value="Log in"/>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>