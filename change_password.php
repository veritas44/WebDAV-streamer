<?php
/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 25-6-2016
 * Time: 22:42
 */
?>

<div class="row">
    <div class="col-md-3">
        <h3>Change password</h3>
    </div>
</div>
<div class="col-md-8">
    <form action="" method="post">
        <div class="form-group">
            <input type="password" placeholder="New password" name="newpass1" id="newpass1" class="form-control input-sm" onkeyup="checkPass()">
        </div>
        <div class="form-group">
            <input type="password" placeholder="New password (again)" name="newpass2" id="newpass2" class="form-control input-sm" onkeyup="checkPass()">
        </div>
        <input type="submit" class="btn btn-primary" id="submitPassword" value="Change password"> <br>
        <div class='alert alert-warning' id="passwordsIncorrect" style="display:none">Passwords do not match or are empty</div>
    </form>
</div>

<script>
    function checkPass() {
        var pass1 = $("#newpass1").val();
        var pass2 = $("#newpass2").val();
        var submitPassword = $("#submitPassword");

        if(pass1 == pass2 && pass1 != ""){
            $("#passwordsIncorrect").hide();
            submitPassword.removeClass('disabled');
            submitPassword.attr("data-toggle", "modal");
        } else {
            $("#passwordsIncorrect").show();
            submitPassword.addClass('disabled');
            submitPassword.removeAttr('data-toggle');
        }
    }
    $(document).ready(function () {
        checkPass();
    });
</script>