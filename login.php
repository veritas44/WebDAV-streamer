<?php
session_start();
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 2-5-2016
 * Time: 22:49
 */
require_once ("config.php");
require_once("class/auth.php");

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
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">

    <script src="js/jquery.particleground.js"></script>
    <script>

    </script>
    <style>
        html, body {
            height: 100%;
            background: #e6e6e6;
        }
        .container {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
        }
        #particles {
            height: 100%;
        }
    </style>
</head>
<body>
<!-- Source: http://bootsnipp.com/snippets/featured/parallax-login-form -->
<div id="particles">
    <div class="container">
        <div class="row vertical-offset-100">
            <div class="col-md-4 col-md-offset-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4><img src="img/logo.svg" alt="Logo" style="height: 35px; width: auto;"> WebDAV streamer</h4>
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
                                <input class="btn btn-lg btn-success btn-block blue" type="submit" value="Login">
                            </fieldset>
                        </form>
                    </div>
                </div>
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