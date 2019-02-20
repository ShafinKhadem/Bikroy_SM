<!DOCTYPE html>
<html>
<head>
    <title>Sign up Demo</title>
    <link rel="stylesheet" href="bootstrap-4.2.1-dist/css/bootstrap.css">
</head>
<body>

<script>
    function validate(caller) {
        if (caller['pswrd'].value!=caller['pswrd2'].value) {
            event.preventDefault();
            var table = caller.childNodes[1];   // table is not a form element unlike input types.
            var row = table.insertRow(-1);
            var cell0 = row.insertCell(0);
            var cell1 = row.insertCell(1);
            cell1.innerHTML = "<font color='red'>Passwords don't match :)</font>";
            return 0;
        }
    }
</script>

<?php

require_once 'vendor/autoload.php';

use BikroySM\Connection as Connection;

try {
    $epdo = Connection::get()->connect();

    if (isset($_POST['signup'])) {
        $epdo->getQueryResults("INSERT INTO users(email, name, password) VALUES('{$_POST['mail']}', '{$_POST['name']}', '{$_POST['pswrd']}');");
        header('location: signin.php');
        exit();
    }
} catch (\PDOException $e) {
    echo $e->getMessage();
}

?>

<h1><center>User registration form</center></h1>
<br><br>
<form class="was-validated" method="post" id="registrationForm" onsubmit="validate(this);">
    <table name="tbl" id="tbl" align="center">
        <tr><td>EMAIL</td>
            <td><input type="email" name="mail" required></td></tr>
        <tr><td>NAME</td>
            <td><input type="text" name="name" value="anonymous" required></td></tr>
        <tr><td>PASSWORD</td>
            <td><input type="password" name="pswrd" pattern=".{8,}" required title="min 8 character"></td></tr>
        <tr><td>retype PASSWORD</td>
            <td><input type="password" name="pswrd2" pattern=".{8,}" required title="both passwords must match"></td></tr>
        <tr><td>&nbsp;</td><td></td></tr>
        <tr><td>
            <div class="custom-control custom-checkbox mb-3">
                <input type="checkbox" class="custom-control-input" name="agree" id="agree" required>
                <label class="custom-control-label" for="agree">Agree to Terms of Service.</label>
                <div class="invalid-feedback">You must agree :)</div>
            </div>
        </td></tr>
        <tr><td></td>
            <td><input type="submit" class="btn btn-info" name="signup" value="Sign up"></td></tr>
    </table>
    <br><p style="background-color: grey; color: yellow">INSERT INTO users(email, name, password) VALUES('{$_POST['mail']}', '{$_POST['name']}', '{$_POST['pswrd']}');</p>
</form>
</body>
</html>