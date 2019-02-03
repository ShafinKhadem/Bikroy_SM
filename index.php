<!DOCTYPE html>
<html>
<head>
    <title>Home page</title>
    <link rel="stylesheet" href="bootstrap.css">
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
    } elseif (isset($_POST['promoteAd'])) {
        $epdo->getFromWhere("pay({$_POST['adid']}, {$_POST['promoteDays']}, {$_POST['amount']}, '{$_POST['transactionid']}')");
        // delete_everything();
    }
    if (isset($_POST['showAds'])) {
        header("location: showAds.php");
        exit();
        // delete_everything();
    }
    if (isset($_POST['runTransactionDemo'])) {
        // run transactionDemo() function written in Epdo.php
        $epdo->transactionDemo();
    }
    if (isset($_POST['runFunctionDemo'])) {
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
    if (isset($_POST['runQuery'])) {
        $rows = $epdo->getQueryResults($_POST['query']);
        if (!isset($rows[0])) {
            echo "empty table";
        } else {
?>

            <div class="container">
                <center><h1>result</h1></center>
                <br><br>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <?php foreach ($rows[0] as $key => $value) : ?>
                                <th><?php echo "{$key}"; ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row) : ?>
                            <tr>
                                <?php foreach ($row as $col) : ?>
                                    <td><?php if (is_bool($col)) var_export($col);    // otherwise boolean false is shown as empty string.
                                                else echo "{$col}"; ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

<?php
        }
        exit();
        // delete_everything();
    }
} catch (\PDOException $e) {
    echo $e->getMessage();
}
?>

<h1><center>Home page</center></h1>
<br><br>
<form method="post">
    <input type="submit" class="btn btn-info" name="signup" value="Sign up">
<?php
if (isset($_SESSION['email'])) {
    echo "Signed in with email: {$_SESSION['email']}";
?>
    <input type="submit" class="btn btn-info" name="signout" value="Sign out">
<?php
} else {
?>
    <input type="submit" class="btn btn-info" name="signin" value="Sign in">
<?php } ?>

    <input type="submit" class="btn btn-info" name="showUser" value="show ur page">
    <br><br>
    <br><br>

    <br><br>
    <!-- <input type="submit" class="btn btn-info" name="runTransactionDemo" value="Run transaction demo"> -->

    <br><br>
    <input type="submit" class="btn btn-info" name="runFunctionDemo" value="show all accounts">

    <br><br>
    <div class="input-group">
        <div class="input-group-prepend">
            <span class="input-group-text">@</span>
        </div>
        <input type="text" name="mail3" placeholder="email" size="30">
        <input type="submit" class="btn btn-info" name="show" value="Show user with this email">
    </div>

    <br><br>
    <input type="submit" class="btn btn-info" name="showAds" value="show all ads">

    <br><br>
    <input type="text" name="adid" placeholder="Ad id">
    <input type="submit" class="btn btn-info" name="showAd" value="Show ad with this id">
    <br>promoteDays: <input type="text" name="promoteDays">
    <br>amount: <input type="text" name="amount">
    <br>transactionid: <input type="text" name="transactionid">
    <input type="submit" class="btn btn-info" name="promoteAd" value="Promote ad with this id">

    <br><br>
    Select query: <br><textarea name="query" rows="10" cols="100"></textarea><br>
    <input type="submit" class="btn btn-info" name="runQuery" value="run query">
</form>
</body>
</html>