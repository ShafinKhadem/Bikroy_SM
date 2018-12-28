<!DOCTYPE html>
<html>
<head>
    <title>Show ad Demo</title>
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
    } elseif ($ad['subcategory']=='motor_cycle') {
        $str = "get_motor_cycle_ad({$_GET['adid']})";
    } elseif ($ad['subcategory']=='mobile_phone') {
        $str = "get_mobile_ad({$_GET['adid']})";
    }
    $ad = $epdo->getFromWhere($str)[0];
    // var_dump($ad);
} catch (\PDOException $e) {
    echo $e->getMessage();
}

?>

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