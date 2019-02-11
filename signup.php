<!DOCTYPE html>
<html>
<head>
    <title>Sign up Demo</title>
    <link rel="stylesheet" href="bootstrap-4.2.1-dist/css/bootstrap.css">
</head>
<body>

<?php

require_once 'vendor/autoload.php';

use BikroySM\Connection as Connection;

try {
    $epdo = Connection::get()->connect();

    if (isset($_POST['signup']) and isset($_POST['agree']) and !empty($_POST['mail']) and filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL) and isset($_POST['name']) and strlen($_POST['pswrd'])>=8) {
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
<form method="post">
    <table align="center">
        <tr><td>EMAIL</td>
            <td><input type="text" name="mail"></td></tr>
        <tr><td>NAME</td>
            <td><input type="text" name="name" value="anonymous"></td></tr>
        <tr><td>PASSWORD</td>
            <td><input type="text" name="pswrd"></td></tr>
        <tr><td>&nbsp;</td><td></td></tr>
        <tr><td>Agree to Terms of Service: </td>
            <td><input type="checkbox" name="agree"></td></tr>
        <?php
        if (isset($_POST['signup'])) {
            // insert a user into the users table
            if (!isset($_POST['agree'])) {
        ?>
                <tr><td></td><td><font color="red">You must agree to terms of service</font></td></tr>
        <?php
            }
            if (empty($_POST['mail']) or !filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL)) {
        ?>
                <tr><td></td><td><font color="red">You must enter a valid email id</font></td></tr>
        <?php
            }
            if (strlen($_POST['pswrd'])<8) {
        ?>
                <tr><td></td><td><font color="red">You must enter password with at least 8 character</font></td></tr>
        <?php
            }
            if (empty($_POST['name'])) {
        ?>
                <tr><td></td><td><font color="red">You must enter name with at least 1 character</font></td></tr>
        <?php
            }
        }
        ?>
        <tr><td></td>
            <td><input type="submit" class="btn btn-info" name="signup" value="Sign up"></td></tr>
    </table>
    <p style="background-color: grey; color: yellow">INSERT INTO users(email, name, password) VALUES('{$_POST['mail']}', '{$_POST['name']}', '{$_POST['pswrd']}');</p>
</form>
</body>
</html>