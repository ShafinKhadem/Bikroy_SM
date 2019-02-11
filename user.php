<!DOCTYPE html>
<html>
<head>
    <title>User page Demo</title>
    <link rel="stylesheet" href="bootstrap-4.2.1-dist/css/bootstrap.css">
</head>
<body>
<?php

require 'vendor/autoload.php';

use BikroySM\Connection as Connection;

session_start();

if (empty($_SESSION['email'])) {
    header('location: signin.php');
} else {
    echo nl2br("Email: {$_SESSION['email']}\nName: {$_SESSION['name']}\n");
}
try {
    $epdo = Connection::get()->connect();
    $isAdmin = $epdo->getFromWhereVal("is_admin('{$_SESSION['email']}')");
    $str = "select chats.sender_mail, chats.message, chats.\"time\", chats.\"date\" from chats where chats.receiver_mail='{$_SESSION['email']}' order by chats.\"date\" desc, chats.\"time\" desc;";
    $notifications = $epdo->getQueryResults($str);
    $str = "select r.ad_id, r.title, l.\"time\", l.\"date\" from (select * from stars where stars.starrer_mail='{$_SESSION['email']}') l left join (select ads.ad_id, ads.title from ads) r on (l.starred_ad_id=r.ad_id) order by l.date desc, l.time desc;";
    $favorites = $epdo->getQueryResults($str);
    $str = "select ads.ad_id, ads.title, ads.\"time\", ads.\"date\" from ads where poster_mail='{$_SESSION['email']}' order by ads.\"date\" desc, ads.\"time\" desc;";
    $posteds = $epdo->getQueryResults($str);
    $str = "select r.ad_id, r.title, l.report_type, l.message, l.\"time\", l.\"date\" from (select * from reports where reports.reporter_mail='{$_SESSION['email']}') l left join (select ads.ad_id, ads.title from ads) r on (l.reported_ad_id=r.ad_id) order by l.date desc, l.time desc;";
    $reporteds = $epdo->getQueryResults($str);
    $cur = $epdo->getFromWhere("users where email = '{$_SESSION['email']}'")[0];
    //var_dump($cur);
    if (isset($_POST['updateUser'])) {
        $epdo->getQueryResults("UPDATE users SET name = '{$_POST['name']}', password = '{$_POST['password']}', location = '{$_POST['location']}', sublocation = '{$_POST['sublocation']}' WHERE email = '{$_SESSION['email']}';");
        $_SESSION['password'] = $_POST['password'];
        header("Refresh:0");
    }
    if (isset($_POST['starAd'])) {
        $epdo->getQueryResults("insert into stars(starred_ad_id, starrer_mail) values({$_POST['adid']}, '{$_SESSION['email']}');");
        header("Refresh:0");
    } else if (isset($_POST['starRemoveAd'])) {
        $epdo->getQueryResults("delete from stars where stars.starred_ad_id={$_POST['adid']} and stars.starrer_mail='{$_SESSION['email']}';");
        header("Refresh:0");
    } else if (isset($_POST['editAd'])) {
        header("location: editAd.php?adid=".$_POST['adid']);
        exit();
    } else if (isset($_POST['deleteAd'])) {
        $epdo->getQueryResults("delete from ads where ads.ad_id={$_POST['adid']} and (ads.poster_mail='{$_SESSION['email']}' or exists(select * from users where users.email='{$_SESSION['email']}' and is_admin='t'));");
        header("Refresh:0");
    } else if (isset($_POST['approveAd'])) {
        $epdo->getQueryResults("update ads set approver_mail='{$_SESSION['email']}' where ad_id={$_POST['adid']} and approver_mail is NULL;");
        header("Refresh:0");
    } else if (isset($_POST['reportAd'])) {
        if (isset($_POST['reportType'])) {
            $epdo->getQueryResults("insert into reports(reported_ad_id, reporter_mail, report_type, message) values({$_POST['adid']}, '{$_SESSION['email']}', '{$_POST['reportType']}', '".str_replace("'", "''", $_POST['messageReport']).'\')');
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
    $epdo->getFromWhere("send_message('{$_SESSION['email']}', '{$_POST['mail']}', '".str_replace("'", "''", $_POST['messageUser']).'\')');
} elseif (isset($_POST['showChats'])) {
    $str = "select chats.sender_mail, chats.receiver_mail, chats.message, chats.\"time\", chats.\"date\" from chats where (chats.sender_mail='{$_SESSION['email']}' and chats.receiver_mail='{$_POST['mail']}') or (chats.sender_mail='{$_POST['mail']}' and chats.receiver_mail='{$_SESSION['email']}') order by chats.\"date\" desc, chats.\"time\" desc;";
    $chats = $epdo->getQueryResults($str);
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
?>

    <br><br>
    <br><br>

    <div class="container">
        <p style="background-color: grey; color: yellow">select chats.sender_mail, chats.message, chats."time", chats."date" from chats where chats.receiver_mail='{$_SESSION['email']}' order by chats."date" desc, chats."time" desc;</p>
        <center><h1><font color="blue">Your notifications 🔔</font></h1></center>
        <br><br>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>notification</th>
                    <th>time</th>
                    <th>date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notifications as $chat) : ?>
                    <tr>
                        <td><?php echo "message from {$chat['sender_mail']}: {$chat['message']}"; ?></td>
                        <td><?php echo $chat['time']; ?></td>
                        <td><?php echo $chat['date']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

<?php
if ($isAdmin) {
?>

    <br><br>
    <br><br>

<h3>IsAdmin function:</h3><p><pre style="background-color: grey; color: yellow">
CREATE OR REPLACE FUNCTION "public"."is_admin"("usermail" varchar)
  RETURNS "pg_catalog"."bool" AS $BODY$
declare
    cnt int;
begin
    select "count"(*) into cnt from users where email=usermail and is_admin='t';
    if cnt=1 then
        return 't';
    else
        return 'f';
    end if;
end; $BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100</pre></p>

    <p style="background-color: grey; color: yellow">select ads.ad_id, ads.title, ads.poster_mail from ads where approver_mail is null order by date asc, time asc;</p>
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
            <?php $approveds = $epdo->getQueryResults("select ads.ad_id, ads.title, ads.poster_mail from ads where approver_mail is null order by date asc, time asc;"); ?>
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

    <p style="background-color: grey; color: yellow">select reported_ad_id, count(*) report_cnt from reports group by reported_ad_id order by report_cnt desc;</p>
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
            <?php $mostReporteds = $epdo->getQueryResults("select reported_ad_id, count(*) report_cnt from reports group by reported_ad_id order by report_cnt desc;"); ?>
            <?php foreach ($mostReporteds as $mostReported) : ?>
                <tr>
                    <td><a href="showAd.php?adid=<?php echo($mostReported['reported_ad_id']); ?>"><?php echo "{$mostReported['reported_ad_id']}"; ?></a></td>
                    <td><?php echo $mostReported['report_cnt']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <br><br>
    <br><br>

    <p style="background-color: grey; color: yellow">select ads.ad_id, ads.title, ads.poster_mail from ads where coalesce(approver_mail,'null')='{$_SESSION['email']}' order by date desc, time desc;</p>
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
            <?php $approveds = $epdo->getQueryResults("select ads.ad_id, ads.title, ads.poster_mail from ads where coalesce(approver_mail,'null')='{$_SESSION['email']}' order by date desc, time desc;"); ?>
            <?php foreach ($approveds as $approved) : ?>
                <tr>
                    <td><a href="showAd.php?adid=<?php echo($approved['ad_id']); ?>"><?php echo "{$approved['ad_id']}"; ?></a></td>
                    <td><?php echo $approved['title']; ?></td>
                    <td><?php echo $approved['poster_mail']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php } ?>

        <br><br>
        <br><br>

        <p style="background-color: grey; color: yellow">select r.ad_id, r.title, l."time", l."date" from<br>
 (select * from stars where<br>
 stars.starrer_mail='{$_SESSION['email']}') l left join<br>
 (select ads.ad_id, ads.title from ads) r on (l.starred_ad_id=r.ad_id)<br>
 order by l.date desc, l.time desc;</p>
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

        <p style="background-color: grey; color: yellow">select ads.ad_id, ads.title, ads."time", ads."date" from ads where poster_mail='{$_SESSION['email']}' order by ads."date" desc, ads."time" desc;</p>
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

        <p style="background-color: grey; color: yellow">select r.ad_id, r.title, l.report_type, l.message, l."time", l."date" from<br>
 (select * from reports where<br>
 reports.reporter_mail='{$_SESSION['email']}') l left join<br>
 (select ads.ad_id, ads.title from ads) r on (l.reported_ad_id=r.ad_id)<br>
 order by l.date desc, l.time desc;</p>
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

        <br><br>
        <p style="background-color: grey; color: yellow">UPDATE users SET name = '{$_POST['name']}', password = '{$_POST['password']}', location = '{$_POST['location']}', sublocation = '{$_POST['sublocation']}' WHERE email = '{$_SESSION['email']}';</p>
        <br><input type="submit" class="btn btn-info" name="updateUser" value="Update ur info">
    </form>

    <br><br>

    <hr>
    <form method="post">
        <br><br>
        Ad Id: <input type="text" name="adid">
        <br><br><p style="background-color: grey; color: yellow">insert into stars(starred_ad_id, starrer_mail) values({$_POST['adid']}, '{$_SESSION['email']}');</p>
        <input type="submit" class="btn btn-info" name="starAd" value="Star ad with this id">
        <br><br><p style="background-color: grey; color: yellow">delete from stars where stars.starred_ad_id={$_POST['adid']} and stars.starrer_mail='{$_SESSION['email']}';</p>
        <input type="submit" class="btn btn-info" name="starRemoveAd" value="Remove star from ad with this id">
        <br><br><input type="submit" class="btn btn-info" name="editAd" value="Edit ad with this id">
        <br><br><p style="background-color: grey; color: yellow">delete from ads where ads.ad_id={$_POST['adid']} and (ads.poster_mail='{$_SESSION['email']}' or exists(select * from users where users.email='{$_SESSION['email']}' and is_admin='t'));</p>
        <input type="submit" class="btn btn-info" name="deleteAd" value="Delete ad with this id">
<?php
if ($isAdmin) {
?>
    <br><br><p style="background-color: grey; color: yellow">update ads set approver_mail='{$_SESSION['email']}' where ad_id={$_POST['adid']} and approver_mail is NULL;</p>
    <h3>Approve trigger:</h3><p><pre style="background-color: grey; color: yellow">
    CREATE OR REPLACE FUNCTION "public"."approve_trigger"()
      RETURNS "pg_catalog"."trigger" AS $BODY$
    DECLARE
        msg varchar;
        price int;
    BEGIN
        msg='ur ad with id '||new.ad_id||' has been approved by '||new.approver_mail;
        perform send_message('bikroy.com', new.poster_mail, msg);
        return new;
    END
    $BODY$
      LANGUAGE plpgsql VOLATILE
      COST 100</pre></p>
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
        <br><br><p style="background-color: grey; color: yellow">"insert into reports(reported_ad_id, reporter_mail, report_type, message) values({$_POST['adid']}, '{$_SESSION['email']}', '{$_POST['reportType']}', '".str_replace("'", "''", $_POST['messageReport']).'\')'</p>
        <h3>Report trigger:</h3><p><pre style="background-color: grey; color: yellow">
    CREATE OR REPLACE FUNCTION "public"."report_trigger"()
      RETURNS "pg_catalog"."trigger" AS $BODY$
    DECLARE
        var record;
        msg varchar;
    BEGIN
        msg='report from '||new.reporter_mail||' on ad '||new.reported_ad_id||' as '||new.report_type||' with message: '||new.message;
        for var in (select email from users where is_admin='t')
        loop
            perform send_message('bikroy.com', var.email, msg);
        end loop;
        return new;
    END
    $BODY$
      LANGUAGE plpgsql VOLATILE
      COST 100</pre></p>
        <input type="submit" class="btn btn-info" name="reportAd" value="Report ad with this id">
    </form>

    <br><hr>

    <br><br>

    <form method="post">

        <input type="submit" class="btn btn-info" name="postAd" value="post ad">

        <br><br><br>

        email: <input type="text" name="mail">
        <br><br><p style="background-color: grey; color: yellow">select chats.sender_mail, chats.receiver_mail, chats.message, chats."time", chats."date" from chats where<br>
 (chats.sender_mail='{$_SESSION['email']}' and chats.receiver_mail='{$_POST['mail']}') or (chats.sender_mail='{$_POST['mail']}' and chats.receiver_mail='{$_SESSION['email']}')<br>
 order by chats."date" desc, chats."time" desc;</p>
        <input type="submit" class="btn btn-info" name="showChats" value="See chats with user with this email">
        <br><br>message: <textarea name="messageUser" rows="5" cols="40"></textarea>

        <br><br>
        <br><br>

        <h3>send message function:</h3><p><pre style="background-color: grey; color: yellow">
        CREATE OR REPLACE FUNCTION "public"."send_message"("_sender_mail" varchar, "_receiver_mail" varchar, "_message" varchar)
          RETURNS "pg_catalog"."void" AS $BODY$
        declare
            cnt int;
        begin
            insert into chats(sender_mail, receiver_mail, message) values(_sender_mail, _receiver_mail, _message);
        end; $BODY$
          LANGUAGE plpgsql VOLATILE
          COST 100</pre></p>
        <input type="submit" class="btn btn-info" name="sendMessage" value="Send message to user with this email">
    </form>

</body>
</html>