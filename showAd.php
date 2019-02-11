<!DOCTYPE html>
<html>
<head>
    <title>Show ad Demo</title>
    <link rel="stylesheet" href="bootstrap-4.2.1-dist/css/bootstrap.css">
</head>
<body>

<?php

require 'vendor/autoload.php';

use BikroySM\Connection as Connection;

try {
    $epdo = Connection::get()->connect();
    $str = "select * from ads v where v.ad_id={$_GET['adid']};";
    $ad = $epdo->getQueryResults($str)[0];
    if ($ad['category']=='electronics') {
        $str = "select * from electronics_ads_view v where v.ad_id={$_GET['adid']};";
    } elseif ($ad['subcategory']=='car') {
        $str = "select * from car_ads_view v where v.ad_id={$_GET['adid']};";
    } elseif ($ad['subcategory']=='motor_cycle') {
        $str = "select * from motor_cycle_ads_view v where v.ad_id={$_GET['adid']};";
    } elseif ($ad['subcategory']=='mobile_phone') {
        $str = "select * from mobile_ads_view v where v.ad_id={$_GET['adid']};";
    }
    $ad = $epdo->getQueryResults($str)[0];
    // var_dump($ad);
} catch (\PDOException $e) {
    echo $e->getMessage();
}

?>
    <br><br><p style="background-color: grey; color: yellow"><?php echo $str; ?></p>
    <center><h1><font color="blue">Your queried ad 🙂</font></h1></center>
    <br><br>
<?php
foreach ($ad as $key => $value) {
    echo "{$key} => ";
    if (is_bool($value)) var_export($value);    // otherwise boolean false is shown as empty string.
    else echo "{$value}";
    echo nl2br("\n");
}
?>
</body>
</html>