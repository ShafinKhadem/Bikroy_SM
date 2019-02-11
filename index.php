<!DOCTYPE html>
<html>
<head>
    <title>Home page</title>
    <link rel="stylesheet" href="bootstrap-4.2.1-dist/css/bootstrap.css">
</head>
<body>

<?php

require 'vendor/autoload.php';

use BikroySM\Connection as Connection;

session_start();

try {
    // connect to the PostgreSQL database
    $epdo = Connection::get()->connect();
    // echo 'A connection to the PostgreSQL database server has been established successfully.';

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
        $epdo->getQueryResults("insert into pay_history(ad_id, promoted_days, amount, transaction_id) values({$_POST['adid']}, {$_POST['promoteDays']}, {$_POST['amount']}, '{$_POST['transactionid']}');");
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
<form method="post"><?php
if (isset($_SESSION['email'])) {
    echo "Signed in with email: {$_SESSION['email']}";
?>
    <input type="submit" class="btn btn-info" name="signout" value="Sign out">
<?php
} else {
?>
    <input type="submit" class="btn btn-info" name="signup" value="Sign up">
    <input type="submit" class="btn btn-info" name="signin" value="Sign in">
<?php } ?>

    <input type="submit" class="btn btn-info" name="showUser" value="show ur page">

    <br><br>
    <!-- <input type="submit" class="btn btn-info" name="runTransactionDemo" value="Run transaction demo"> -->

    <br><br>
    <input type="submit" class="btn btn-info" name="showAds" value="show all ads">

    <br><br>
    <input type="text" name="adid" placeholder="Ad id">
    <input type="submit" class="btn btn-info" name="showAd" value="Show ad with this id">
    <br>promoteDays: <input type="text" name="promoteDays">
    <br>amount: <input type="text" name="amount">
    <br>transactionid: <input type="text" name="transactionid">
    <br><br><p style="background-color: grey; color: yellow">insert into pay_history(ad_id, promoted_days, amount, transaction_id) values({$_POST['adid']}, {$_POST['promoteDays']}, {$_POST['amount']}, '{$_POST['transactionid']}');</p>
    <h3>Pay trigger:</h3><p><pre style="background-color: grey; color: yellow">
    CREATE OR REPLACE FUNCTION "public"."pay_trigger"()
    RETURNS "pg_catalog"."trigger" AS $BODY$
    DECLARE
        var record;
        msg varchar;
    BEGIN
        msg='payment for ad '||new.ad_id||' with promotion days: '||new.promoted_days;
        for var in (select email from users where is_admin='t')
        loop
            perform send_message('bikroy.com', var.email, msg);
        end loop;
        return new;
    END
    $BODY$
      LANGUAGE plpgsql VOLATILE
      COST 100</pre></p>
    <input type="submit" class="btn btn-info" name="promoteAd" value="Pay for ad with this id">

    <br><br>
    Select query: <br><textarea name="query" rows="10" cols="100"></textarea><br>
    <input type="submit" class="btn btn-info" name="runQuery" value="run query">
</form>
</body>
</html>