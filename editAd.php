<!DOCTYPE html>
<html>
<head>
    <title>Edit ad Demo</title>
    <link rel="stylesheet" href="https://cdn.rawgit.com/twbs/bootstrap/v4-dev/dist/css/bootstrap.css">
</head>
<body>

<?php

require 'vendor/autoload.php';

use BikroySM\Connection as Connection;
use BikroySM\Epdo as Epdo;

try {
    $pdo = Connection::get()->connect();
    $epdo = new Epdo($pdo);
    $str = "get_others_ad({$_GET['adid']})";
    $ad = $epdo->getFromWhere($str)[0];
    if ($ad['category']=='electronics') {
        $str = "get_electronics_ad({$_GET['adid']})";
    } elseif ($ad['subcategory']=='car') {
        $str = "get_car_ad({$_GET['adid']})";
        $str2 = 'edit_car_ad';
    } elseif ($ad['subcategory']=='motor_cycle') {
        $str = "get_motor_cycle_ad({$_GET['adid']})";
        $str2 = 'edit_motor_cycle_ad';
    } elseif ($ad['subcategory']=='mobile_phone') {
        $str = "get_mobile_ad({$_GET['adid']})";
        $str2 = 'edit_mobile_ad';
    }
    $ad = $epdo->getFromWhere($str)[0];
    if (isset($_POST['saveEdit'])) {
        $param = 'row(';
        foreach ($ad as $key => $value) {
            if ($param!='row(') $param = $param.', ';
            $param = $param.'\'';
            if ($key=='ad_id'or $key=='category'or $key=='subcategory' or $key=='poster_mail' or $key=='approver_mail' or $key=='time' or $key=='date') $param = $param.$value;
            else $param = $param.$_POST[$key];
            $param = $param.'\'';
        }
        $param = $param.')';
        $param = str_replace("''", "null", $param);
        // echo $param;
        $epdo->getFromWhere("{$str2}({$param})");
        header("location: showAd.php?adid=".$_GET['adid']);
        exit();
    }
} catch (\PDOException $e) {
    echo $e->getMessage();
}

?>

    <center><h1><font color="blue">Your wannaEdit ad 🙂</font></h1></center><br><br>
    <form method="post">
<?php
foreach ($ad as $key => $value) {
    echo "{$key} => ";
    if ($key=='ad_id'or $key=='category'or $key=='subcategory' or $key=='poster_mail' or $key=='approver_mail' or $key=='time' or $key=='date') echo ($value);
    else {
?>
    <textarea name="<?php echo($key); ?>" cols=50><?php if (is_bool($value)) var_export($value); else echo "{$value}"; ?></textarea>
<?php
    }
    echo nl2br("\n");
}
?>
    <br><input type="submit" name="saveEdit" value="save">
    </form>
</body>
</html>