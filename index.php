<!DOCTYPE html>
<html>
<head>
    <title>Home page</title>
    <link rel="stylesheet" href="https://cdn.rawgit.com/twbs/bootstrap/v4-dev/dist/css/bootstrap.css">
</head>
<body>

<?php

require 'vendor/autoload.php';

use BikroySM\Connection as Connection;
use BikroySM\Epdo as Epdo;

session_start();

try {
    // connect to the PostgreSQL database
    $pdo = Connection::get()->connect();
    // echo 'A connection to the PostgreSQL database server has been established successfully.';
    $epdo = new Epdo($pdo);

    if (isset($_POST['signup'])) {
        header("location: signup.php");
        exit();
        // delete_everything();
    }
    if (isset($_POST['signin'])) {
        header("location: signin.php");
        exit();
        // delete_everything();
    }
    if (isset($_POST['signout'])) {
        header("location: signout.php");
        exit();
        // delete_everything();
    }
    if (isset($_POST['showUser'])) {
        header("location: user.php");
        exit();
        // delete_everything();
    }
    if (isset($_POST['showAd'])) {
        header("location: showAd.php?adid=".$_POST['adid']);
        exit();
        // delete_everything();
    }
    if (isset($_POST['runTransactionDemo'])) {
        // run transactionDemo() function written in Epdo.php
        $epdo->transactionDemo();
    }
    if (isset($_POST['runFunctionDemo'])) {
        // run add() in Epdo.php which calls add() in database & directly get_account() in database.
        // $result = $epdo->add(20, 30);
        // echo $result;
        $accounts = $epdo->getFromWhere('get_accounts()');
?>

        <div class="container">
            <center><h1>Account List</h1></center>
            <br><br>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>email</th>
                        <th>Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accounts as $account) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($account['email']) ?></td>
                            <td><?php echo htmlspecialchars($account['name']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

<?php
        exit();
        // delete_everything();
    }
    if (isset($_POST['show'])) {
        $users = $epdo->findByPK($_POST['mail3']);
?>

        <div class="container">
            <center><h1>Users List</h1></center>
            <br><br>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>email</th>
                        <th>name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['email']) ?></td>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

<?php
        exit();
    }
} catch (\PDOException $e) {
    echo $e->getMessage();
}
?>

<h1><center>Home page</center></h1>
<br><br>
<form method="post">
    <input type="submit" name="signup" value="Sign up">
<?php
if (isset($_SESSION['email'])) {
    echo "{$_SESSION['email']}";
?>
    <input type="submit" name="signout" value="Sign out">
<?php
} else {
?>
    <input type="submit" name="signin" value="Sign in">
<?php } ?>

    <input type="submit" name="showUser" value="show ur page">
    <br><br>
    <br><br>

    <br><br>
    <input type="submit" name="runTransactionDemo" value="Run transaction demo">

    <br><br>
    <input type="submit" name="runFunctionDemo" value="show all (Run function demo)">

    <br><br>
    <table>
        <tr><td>EMAIL</td>
            <td><input type="text" name="mail3"></td></tr>
        <tr><td>&nbsp;</td><td></td></tr>
        <tr><td></td>
            <td><input type="submit" name="show" value="Show user with this email"></td></tr>
    </table>

    <br><br>
    <table>
        <tr><td>Ad ID</td>
            <td><input type="text" name="adid"></td></tr>
        <tr><td>&nbsp;</td><td></td></tr>
        <tr><td></td>
            <td><input type="submit" name="showAd" value="Show ad with this id"></td></tr>
    </table>
</form>
</body>
</html>