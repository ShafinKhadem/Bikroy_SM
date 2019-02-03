<!DOCTYPE html>
<html>
<head>
    <title>Sign in Demo</title>
    <link rel="stylesheet" href="bootstrap.css">
</head>
<body>

<?php

require_once 'vendor/autoload.php';

use BikroySM\Connection as Connection;
use BikroySM\Epdo as Epdo;

session_start();

try {
    // connect to the PostgreSQL database
    $pdo = Connection::get()->connect();
    // echo 'A connection to the PostgreSQL database server has been established successfully.';
    $epdo = new Epdo($pdo);
} catch (\PDOException $e) {
    echo $e->getMessage();
}

?>

<h1><center>Sign in using email and password</center></h1>
<br><br>
<form method="post">
    <table align="center">
        <tr><td>EMAIL</td>
            <td><input type="text" name="mail"></td></tr>
        <tr><td>PASSWORD</td>
            <td><input type="text" name="pswrd"></td></tr>
        <tr><td>&nbsp;</td><td></td></tr>
        <tr><td></td>
            <td><input type="submit" class="btn btn-info" name="signin" value="Sign in"></td></tr>
    </table>
<?php
if (isset($_POST['signin'])) {
    // retrieves this user from the users table if exists
    $users = $epdo->getFromWhere("signin('{$_POST['mail']}', '{$_POST['pswrd']}')");
        // print_r($users);
    if (count($users)!=1) {
?>
        <tr><td></td><td><font color="red">Enter email ID and password which u used for signup.</font></td></tr>
<?php
    } else {
        $_SESSION['email'] = $users[0]['email'];
        $_SESSION['name'] = $users[0]['name'];
        $_SESSION['password'] = $users[0]['password'];
        header('location: user.php');
        exit();
    }
}
?>
</form>
</body>
</html>