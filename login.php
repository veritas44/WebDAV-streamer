<?php
session_start();
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 2-5-2016
 * Time: 22:49
 */
require_once ("config.php");
require_once("class/database.php");
require_once("class/auth.php");

$message = "";

if(isset($_GET["logout"])){
    session_destroy();
}

if(basename($_SERVER['PHP_SELF']) != "login.php"){
    header("Location: login.php");
    die();
}

$auth = new Auth();

/*
if(key_exists("autologin", $auth->users)){
    $_SESSION["username"] = "autologin";
    $_SESSION["password"] = $auth->users["autologin"]["password_streamer"];
    header("Location: index.php");
    die();
}
*/

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
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">

    <link rel="apple-touch-icon" sizes="57x57" href="apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="manifest" href="manifest.json">
    <meta name="msapplication-TileColor" content="#2b3e50">
    <meta name="msapplication-TileImage" content="ms-icon-144x144.png">
    <meta name="theme-color" content="#2b3e50">

    <style>
        html, body {
            height: 100%;
            width: 100%;
        }
        .container {
            position: absolute;
            width: 500px;
            top: 50%;
            left: 50%;
            -webkit-transform: translate(-50%, -50%);
            -ms-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
        }
        #particles {
            height: 100%;
        }

        @media (max-width: 640px) {
            .container {
                width: 100%;
            }

            .col-md-4 {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<!-- Source: http://bootsnipp.com/snippets/featured/parallax-login-form -->
<div id="particles">
    <div class="container">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <img src="img/logo_white.svg" alt="Logo" style="height: 35px; width: auto; float: left"><h4 style="vertical-align: middle;"> &nbsp;WebDAV streamer</h4>
                </div>
                <div class="panel-body">
                    <form method="post" action="login.php">
                        <?php
                        if($message != "") {
                            echo '<div class="alert alert-warning">';
                            echo $message;
                            echo '</div>';
                        }
                        ?>
                        <fieldset>
                            <div class="form-group">
                                <input class="form-control" type="text" name="username" placeholder="Username" value="<?php echo (isset($_POST["username"]) ? $_POST["username"] : ""); ?>"/>
                            </div>
                            <div class="form-group">
                                <input class="form-control" type="password" name="password" placeholder="Password" />
                            </div>
                            <input class="btn btn-lg btn-block btn-primary" type="submit" value="Login">
                        </fieldset>
                    </form>
                </div>
                </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        particleground(document.getElementById('particles'), {
            dotColor: '#aaaaaa',
            lineColor: '#aaaaaa'
        });
        var intro = document.getElementById('intro');
        intro.style.marginTop = - intro.offsetHeight / 2 + 'px';
    }, false);
</script>
</body>
</html>