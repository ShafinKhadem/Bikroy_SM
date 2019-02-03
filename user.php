<!DOCTYPE html>
<html>
<head>
    <title>User page Demo</title>
    <link rel="stylesheet" href="bootstrap.css">
</head>
<body>

<?php

require 'vendor/autoload.php';

use BikroySM\Connection as Connection;
use BikroySM\Epdo as Epdo;

session_start();

if (empty($_SESSION['email'])) {
    header('location: signin.php');
} else {
    echo nl2br("Email: {$_SESSION['email']}\nName: {$_SESSION['name']}\n");
}
try {
    $pdo = Connection::get()->connect();
    $epdo = new Epdo($pdo);
    $isAdmin = $epdo->getFromWhereVal("is_admin('{$_SESSION['email']}')");
    $str = "get_favorites('{$_SESSION['email']}')";
    $favorites = $epdo->getFromWhere($str);
    $str = "get_posts('{$_SESSION['email']}')";
    $posteds = $epdo->getFromWhere($str);
    $str = "get_reports('{$_SESSION['email']}')";
    $reporteds = $epdo->getFromWhere($str);
    $cur = $epdo->getFromWhere("users where email = '{$_SESSION['email']}'")[0];
    //var_dump($cur);
    if (isset($_POST['updateUser'])) {
        $epdo->getFromWhere("update_user('{$_SESSION['email']}', '{$_POST['name']}', '{$_POST['password']}', '{$_POST['location']}', '{$_POST['sublocation']}')");
        $_SESSION['password'] = $_POST['password'];
        header("Refresh:0");
    }
    if (isset($_POST['starAd'])) {
        $epdo->getFromWhere("star_ad({$_POST['adid']}, '{$_SESSION['email']}')");
        header("Refresh:0");
    } else if (isset($_POST['starRemoveAd'])) {
        $epdo->getFromWhere("star_remove_ad({$_POST['adid']}, '{$_SESSION['email']}')");
        header("Refresh:0");
    } else if (isset($_POST['editAd'])) {
        header("location: editAd.php?adid=".$_POST['adid']);
        exit();
    } else if (isset($_POST['deleteAd'])) {
        $epdo->getFromWhere("delete_ad({$_POST['adid']}, '{$_SESSION['email']}')");
        header("Refresh:0");
    } else if (isset($_POST['approveAd'])) {
        $epdo->getFromWhere("approve_ad({$_POST['adid']}, '{$_SESSION['email']}')");
        header("Refresh:0");
    } else if (isset($_POST['reportAd'])) {
        if (isset($_POST['reportType'])) {
            $epdo->getFromWhere("report_ad({$_POST['adid']}, '{$_SESSION['email']}', '{$_POST['reportType']}', ".str_replace("'", "''", $_POST['messageReport']).'\')');
            header("Refresh:0");
        } else {
            echo "report type is must";
        }
    } elseif (isset($_POST['postAd'])) {
        header('location: postAd.php');
        exit();
    }
} catch (\PDOException $e) {
    echo $e->getMessage();
}

if (isset($_POST['sendMessage'])) {
    $epdo->getFromWhere("send_message('{$_SESSION['email']}', '{$_POST['mail']}', ".str_replace("'", "''", $_POST['messageUser']).'\')');
} elseif (isset($_POST['showChats'])) {
    $str = "get_chats('{$_SESSION['email']}', '{$_POST['mail']}')";
    $chats = $epdo->getFromWhere($str);
?>
    <div class="container">
        <center><h1><font color="blue">Your queried chats 🙂</font></h1></center>
        <br><br>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>sender_mail</th>
                    <th>receiver_mail</th>
                    <th>message</th>
                    <th>time</th>
                    <th>date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($chats as $chat) : ?>
                    <tr>
                        <td><?php echo $chat['sender_mail']; ?></td>
                        <td><?php echo $chat['receiver_mail']; ?></td>
                        <td><?php echo $chat['message']; ?></td>
                        <td><?php echo $chat['time']; ?></td>
                        <td><?php echo $chat['date']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php
}
if ($isAdmin) {
?>

    <br><br>
    <br><br>

    <div class="container">
        <center><h1><font color="orange">Your approved ads 👍</font></h1></center>
        <br><br>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ad_id</th>
                    <th>title</th>
                    <th>poster_mail</th>
                </tr>
            </thead>
            <tbody>
                <?php $approveds = $epdo->getFromWhere("get_approved_by('{$_SESSION['email']}')"); ?>
                <?php foreach ($approveds as $approved) : ?>
                    <tr>
                        <td><a href="showAd.php?adid=<?php echo($approved['ad_id']); ?>"><?php echo "{$approved['ad_id']}"; ?></a></td>
                        <td><?php echo $approved['title']; ?></td>
                        <td><?php echo $approved['poster_mail']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <br><br>
        <br><br>

        <center><h1><font color="red">Ads waiting approval 😋</font></h1></center>
        <br><br>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ad_id</th>
                    <th>title</th>
                    <th>poster_mail</th>
                </tr>
            </thead>
            <tbody>
                <?php $approveds = $epdo->getFromWhere("get_approved_by('null')"); ?>
                <?php foreach ($approveds as $approved) : ?>
                    <tr>
                        <td><a href="showAd.php?adid=<?php echo($approved['ad_id']); ?>"><?php echo "{$approved['ad_id']}"; ?></a></td>
                        <td><?php echo $approved['title']; ?></td>
                        <td><?php echo $approved['poster_mail']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <br><br>
        <br><br>

        <center><h1><font color="red">Most reported ads ⚠</font></h1></center>
        <br><br>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ad_id</th>
                    <th>report_cnt</th>
                </tr>
            </thead>
            <tbody>
                <?php $mostReporteds = $epdo->getFromWhere("get_reporteds()"); ?>
                <?php foreach ($mostReporteds as $mostReported) : ?>
                    <tr>
                        <td><a href="showAd.php?adid=<?php echo($mostReported['ad_id']); ?>"><?php echo "{$mostReported['ad_id']}"; ?></a></td>
                        <td><?php echo $mostReported['report_cnt']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>


<?php
}
?>

    <br><br>
    <br><br>

    <div class="container">
        <center><h1><font color="green">Your favorite ads 💜</font></h1></center>
        <br><br>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ad_id</th>
                    <th>title</th>
                    <th>starring_time</th>
                    <th>starring_date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($favorites as $favorite) : ?>
                    <tr>
                        <td><a href="showAd.php?adid=<?php echo($favorite['ad_id']); ?>"><?php echo "{$favorite['ad_id']}"; ?></a></td>
                        <td><?php echo $favorite['title']; ?></td>
                        <td><?php echo $favorite['time']; ?></td>
                        <td><?php echo $favorite['date']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <br><br>
        <br><br>

        <center><h1><font color="yellow">Your posted ads 🙂</font></h1></center>
        <br><br>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ad_id</th>
                    <th>title</th>
                    <th>time</th>
                    <th>date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posteds as $posted) : ?>
                    <tr>
                        <td><a href="showAd.php?adid=<?php echo($posted['ad_id']); ?>"><?php echo "{$posted['ad_id']}"; ?></a></td>
                        <td><?php echo $posted['title']; ?></td>
                        <td><?php echo $posted['time']; ?></td>
                        <td><?php echo $posted['date']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <br><br>
        <br><br>

        <center><h1><font color="red">Your reported ads 😠</font></h1></center>
        <br><br>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ad_id</th>
                    <th>title</th>
                    <th>report_type</th>
                    <th>message</th>
                    <th>reporting_time</th>
                    <th>reporting_date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reporteds as $reported) : ?>
                    <tr>
                        <td><a href="showAd.php?adid=<?php echo($reported['ad_id']); ?>"><?php echo "{$reported['ad_id']}"; ?></a></td>
                        <td><?php echo $reported['title']; ?></td>
                        <td><?php echo $reported['report_type']; ?></td>
                        <td><?php echo $reported['message']; ?></td>
                        <td><?php echo $reported['time']; ?></td>
                        <td><?php echo $reported['date']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <br><br>
    <br><br>

    <h1><center>User operations form</center></h1>

    <br><br>
    <br><br>

    <form method="post">

<?php
foreach ($cur as $key => $value) {
    if ($key=='email' or $key=='is_admin') continue;
?>
        <br><?php echo "{$key}"; ?>: <input type="text" name="<?php echo "{$key}"; ?>" value="<?php echo("{$value}"); ?>">
<?php
}
?>

        <br>
        <br><input type="submit" class="btn btn-info" name="updateUser" value="Update ur info">
    </form>

    <br><br>

    <hr>
    <form method="post">
        <br><br>
        Ad Id: <input type="text" name="adid">
        <br><br><input type="submit" class="btn btn-info" name="starAd" value="Star ad with this id">
        <input type="submit" class="btn btn-info" name="starRemoveAd" value="Remove star from ad with this id">
        <input type="submit" class="btn btn-info" name="editAd" value="Edit ad with this id">
        <input type="submit" class="btn btn-info" name="deleteAd" value="Delete ad with this id">
<?php
if ($isAdmin) {
?>
    <input type="submit" class="btn btn-info" name="approveAd" value="Approve ad with this id">
<?php
}
?>
        <br><br>
        message: <textarea name="messageReport" rows="5" cols="40"></textarea>
        <br>reportType:
        <input type="radio" name="reportType" value="spam">spam
        <input type="radio" name="reportType" value="unavailable">unavailable
        <input type="radio" name="reportType" value="fraud">fraud
        <input type="radio" name="reportType" value="duplicate">duplicate
        <input type="radio" name="reportType" value="wrong category">wrong category
        <input type="radio" name="reportType" value="other">Other
        <br><br><input type="submit" class="btn btn-info" name="reportAd" value="Report ad with this id">
    </form>

    <br><hr>

    <br><br>

    <form method="post">

        <input type="submit" class="btn btn-info" name="postAd" value="post ad">

        <br><br><br>

        email: <input type="text" name="mail">
        <br><br><input type="submit" class="btn btn-info" name="showChats" value="See chats with user with this email">
        <br><br>message: <textarea name="messageUser" rows="5" cols="40"></textarea>
        <br><br><input type="submit" class="btn btn-info" name="sendMessage" value="Send message to user with this email">
    </form>

</body>
</html>