<!DOCTYPE html>
<html>
<head>
    <title>Edit ad Demo</title>
    <link rel="stylesheet" href="bootstrap.css">
</head>
<body>

<?php

require 'vendor/autoload.php';

use BikroySM\Connection as Connection;
use BikroySM\Epdo as Epdo;

session_start();

try {
    $pdo = Connection::get()->connect();
    $epdo = new Epdo($pdo);
    $str = "check_edit_access({$_GET['adid']}, '{$_SESSION['email']}')";
    if (!$epdo->getFromWhereVal($str)) {
        echo "U r neither admin nor poster of this ad. How dare u try to edit this ad 😠";
        exit();
    }
    $str = "get_others_ad({$_GET['adid']})";
    $ad = $epdo->getFromWhere($str)[0];
    if ($ad['category']=='electronics') {
        $str = "get_electronics_ad({$_GET['adid']})";
        $str2 = 'edit_electronics_ad';
    } else {
        $str = "get_".$ad['subcategory']."_ad({$_GET['adid']})";
        $str2 = 'edit_'.$ad['subcategory'].'_ad';
    }
    $ad = $epdo->getFromWhere($str)[0];
    if (isset($_POST['saveEdit'])) {
        $param = 'row(';
        foreach ($ad as $key => $value) {
            if ($param!='row(') $param = $param.', ';
            if ($key=='ad_id'or $key=='category'or $key=='subcategory' or $key=='poster_mail' or $key=='approver_mail' or $key=='time' or $key=='date') $param = $param.'\''.$value.'\'';
            elseif (empty($_POST[$key])) $param = $param.'null';
            else $param = $param.'\''.str_replace("'", "''", $_POST[$key]).'\'';
        }
        $param = $param.')';
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
    <br><input type="submit" class="btn btn-info" name="saveEdit" value="save">
    </form>
</body>
</html>