<!DOCTYPE html>
<html>
<head>
    <title>Post ad Demo</title>
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
    echo nl2br("Email: ".$_SESSION['email']."\n"."Name: ".$_SESSION['name']."\n");
}
try {
    $pdo = Connection::get()->connect();
    $epdo = new Epdo($pdo);

    // $adatrspost = array('0' => 'buy_or_sell', '1' => 'poster_phone', '2' => 'price', '3' => 'is_negotiable', '4' => 'title', '5' => 'details', '6' => 'category', '7' => 'subcategory', '8' => 'location', '9' => 'sublocation');
    if (isset($_POST['postAd'])) {
        $tit = str_replace("'", "'__'", $_POST['title']);
        $det = str_replace("'", "'__'", $_POST['details']);
        $str = "post_ad('{$_POST['buy_or_sell']}', '{$_POST['poster_phone']}', '{$_POST['price']}', '{$_POST['is_negotiable']}', '{$tit}'
        , '{$det}', '{$_POST['category']}', '{$_POST['subcategory']}', '{$_POST['location']}', '{$_POST['sublocation']}', '{$_SESSION['email']}')";
        $str = str_replace("''", "null", $str);
        $str = str_replace("'__'", "''", $str);
        // echo $str;
        $adid = $epdo->getFromWhereVal($str);
        if ($_POST['category']=='electronics') {
            $str = "post_electronics_ad('{$adid}', '{$_POST['brand']}', '{$_POST['model']}')";
        } elseif ($_POST['subcategory']=='car') {
            $str = "post_car_ad('{$adid}', '{$_POST['brand']}', '{$_POST['model']}', '{$_POST['edition']}', '{$_POST['model_year']}', '{$_POST['condition']}'
            , '{$_POST['transmission']}', '{$_POST['body_type']}', '{$_POST['fuel_type']}', '{$_POST['engine_capacity']}', '{$_POST['kilometers_run']}')";
        } elseif ($_POST['subcategory']=='motor_cycle') {
            $str = "post_motor_cycle_ad('{$adid}', '{$_POST['bike_type']}', '{$_POST['brand']}', '{$_POST['model']}', '{$_POST['model_year']}', '{$_POST['condition']}'
            , '{$_POST['engine_capacity']}', '{$_POST['kilometers_run']}')";
        } elseif ($_POST['subcategory']=='mobile_phone') {
            $str = "post_mobile_ad('{$adid}', '{$_POST['brand']}', '{$_POST['model']}', '{$_POST['edition']}', '{$_POST['features']}', '{$_POST['authenticity']}'
            , '{$_POST['condition']}')";
        }
        if ($_POST['category']!='others') {
            $str = str_replace("''", "null", $str);
            // echo $str;
            $epdo->getFromWhere($str);
        }
        header('location: user.php');
    }
} catch (\PDOException $e) {
    echo $e->getMessage();
}

?>

    <h1><center>Posting ad form</center></h1><br><br>
    <form method="post">

        sell or buy:
        <input type="radio" name="buy_or_sell" value="1" <?php if(isset($_POST['buy_or_sell']) and $_POST['buy_or_sell']=='1')  echo 'checked="checked"';?> >sell
        <input type="radio" name="buy_or_sell" value="0" <?php if(isset($_POST['buy_or_sell']) and $_POST['buy_or_sell']=='0')  echo 'checked="checked"';?> >buy

        <br>

        poster_phone: <input type="text" name="poster_phone" value="<?php if (isset($_POST['poster_phone'])) echo $_POST['poster_phone']; ?>"> <br>
        price: <input type="text" name="price" value="<?php if (isset($_POST['price'])) echo $_POST['price']; ?>">

        is_negotiable:
        <input type="radio" name="is_negotiable" value="1" <?php if(isset($_POST['is_negotiable']) and $_POST['is_negotiable']=='1')  echo 'checked="checked"';?> >Yes
        <input type="radio" name="is_negotiable" value="0" <?php if(isset($_POST['is_negotiable']) and $_POST['is_negotiable']=='0')  echo 'checked="checked"';?> >No

        <br>

        title: <input type="text" name="title" size="90" value="<?php if (isset($_POST['title'])) echo $_POST['title']; ?>"> <br>

        details:<br>
        <textarea id="details" name="details"  rows="10" cols="100"><?php if(isset($_POST['details'])) echo htmlentities ($_POST['details']); ?></textarea>

        <br>

        <br><br>

        location:
<?php
$locations = $epdo->getFromWhereCol('get_locations()');
foreach ($locations as $location) {
?>
    <input type="radio" name="location" value="<?php echo($location); ?>" <?php if(isset($_POST['location']) and $_POST['location']==$location)  echo 'checked="checked"';?> ><?php echo $location; ?>
<?php
}
?>

        <br>

        sublocation:
<?php
if (isset($_POST['location'])) {
    $sublocations = $epdo->getFromWhereCol("get_sublocations('{$_POST['location']}')");
    foreach ($sublocations as $sublocation) {
?>
        <input type="radio" name="sublocation" value="<?php echo($sublocation); ?>" <?php if(isset($_POST['sublocation']) and $_POST['sublocation']==$sublocation)  echo 'checked="checked"';?> ><?php echo $sublocation; ?>
<?php
    }
}
?>

        <br><br>

        category:
<?php
$categories = $epdo->getFromWhereCol('get_categories()');
foreach ($categories as $category) {
?>
    <input type="radio" name="category" value="<?php echo($category); ?>" <?php if(isset($_POST['category']) and $_POST['category']==$category)  echo 'checked="checked"';?> ><?php echo $category; ?>
<?php
}
?>

        <br>

        subcategory:
<?php
if (isset($_POST['category'])) {
    $subcategories = $epdo->getFromWhereCol("get_subcategories('{$_POST['category']}')");
    foreach ($subcategories as $subcategory) {
?>
        <input type="radio" name="subcategory" value="<?php echo($subcategory); ?>" <?php if(isset($_POST['subcategory']) and $_POST['subcategory']==$subcategory)  echo 'checked="checked"';?> ><?php echo $subcategory; ?>
<?php
    }
    if (isset($_POST['subcategory']) and $_POST['category']!='others') {
        $str = $_POST['subcategory'].'_ads';
        if ($_POST['category']=='electronics') {
            $str = 'electronics_ads';
        }
        $attrs = $epdo->getFromWhereCol("get_column_names('{$str}')");
        foreach ($attrs as $attr) {
            if ($attr=='ad_id') continue;
?>
            <br><?php echo "{$attr}"; ?>: <input type="text" name="<?php echo($attr); ?>">
<?php
        }
    }
}
?>
        <br><br>

        <input type="submit" class="btn btn-info" name="next" value="next">

        <br><br>
        <input type="submit" class="btn btn-info" name="postAd" value="postAd">
    </form>

</body>
</html>