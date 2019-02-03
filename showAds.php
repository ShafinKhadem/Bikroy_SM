<!DOCTYPE html>
<html>
<head>
    <title>Show ad Demo</title>
    <link rel="stylesheet" href="bootstrap.css">
</head>
<body>

<?php

require 'vendor/autoload.php';

use BikroySM\Connection as Connection;
use BikroySM\Epdo as Epdo;

try {
    $pdo = Connection::get()->connect();
    $epdo = new Epdo($pdo);
    $str = "ads where approver_mail is not null";
    if ((isset($_POST['location']) and $_POST['location']!='any') or (isset($_POST['category']) and $_POST['category']!='any')) {
        if (isset($_POST['location']) and $_POST['location']!='any') {
            $str = $str.' and ';
            $str = $str."location='{$_POST['location']}'";
        }
        if (isset($_POST['sublocation']) and $_POST['sublocation']!='any') {
            $str = $str.' and ';
            $str = $str."sublocation='{$_POST['sublocation']}'";
        }
        if (isset($_POST['category']) and $_POST['category']!='any') {
            $str = $str.' and ';
            $str = $str."category='{$_POST['category']}'";
        }
        if (isset($_POST['subcategory']) and $_POST['subcategory']!='any') {
            $str = $str.' and ';
            $str = $str."subcategory='{$_POST['subcategory']}'";
        }
    }
    $str2 = "(select * from ".$str.") l join (select distinct ad_id from pay_history where CURRENT_TIMESTAMP-pay_history.\"time\"<\"interval\"(pay_history.promoted_days||' day')) r on (l.ad_id=r.ad_id)"." order by random() limit 2";
    if (isset($_POST['sort']) and $_POST['sort']!='random') {
        $str = $str." order by {$_POST['sort']}";
    }

    // echo $str;
    // echo htmlspecialchars($str2);
    $ads = $epdo->getFromWhere($str);
    $topads = $epdo->getFromWhere($str2);
    // var_dump($ads);
} catch (\PDOException $e) {
    echo $e->getMessage();
}

?>

<form method="post">
    Sort results by:
    <select name="sort">
        <option value="random" <?php if (isset($_POST['sort']) and $_POST['sort']=="random") { ?>selected="true" <?php }; ?> >random order</option>
        <option value="date desc, time desc" <?php if (isset($_POST['sort']) and $_POST['sort']=="date desc, time desc") { ?>selected="true" <?php }; ?> >Time: newest on top</option>
        <option value="date asc, time asc" <?php if (isset($_POST['sort']) and $_POST['sort']=="date asc, time asc") { ?>selected="true" <?php }; ?> >Time: oldest on top</option>
        <option value="price asc" <?php if (isset($_POST['sort']) and $_POST['sort']=="price asc") { ?>selected="true" <?php }; ?> >Price: low to high</option>
        <option value="price desc" <?php if (isset($_POST['sort']) and $_POST['sort']=="price desc") { ?>selected="true" <?php }; ?> >Price: high to low</option>
    </select>

    <br><br>
    <h4>Filter results by:</h4>

    location:
    <input type="radio" name="location" value="any" <?php if(isset($_POST['location']) and $_POST['location']=='any')  echo 'checked="checked"';?> >any
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
?>
    <input type="radio" name="sublocation" value="any" <?php if(isset($_POST['sublocation']) and $_POST['sublocation']=='any')  echo 'checked="checked"';?> >any
<?php
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
    <input type="radio" name="category" value="any" <?php if(isset($_POST['category']) and $_POST['category']=='any')  echo 'checked="checked"';?> >any
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
?>
    <input type="radio" name="subcategory" value="any" <?php if(isset($_POST['subcategory']) and $_POST['subcategory']=='any')  echo 'checked="checked"';?> >any
<?php
    $subcategories = $epdo->getFromWhereCol("get_subcategories('{$_POST['category']}')");
    foreach ($subcategories as $subcategory) {
?>
        <input type="radio" name="subcategory" value="<?php echo($subcategory); ?>" <?php if(isset($_POST['subcategory']) and $_POST['subcategory']==$subcategory)  echo 'checked="checked"';?> ><?php echo $subcategory; ?>
<?php
    }
}
?>

    <br><br>
    <input type="submit" class="btn btn-info" name="showAds" value="show ads">
</form>

<br><br>

top ads:<br>
<?php
foreach ($topads as $ad) {
?>
    <a href="showAd.php?adid=<?php echo($ad['ad_id']); ?>">id: <?php echo "{$ad['ad_id']}"; ?>, title: <?php echo "{$ad['title']}"; ?></a>
    <br>
<?php
}
?>
<br><br>ads:<br>
<?php
foreach ($ads as $ad) {
?>
    <a href="showAd.php?adid=<?php echo($ad['ad_id']); ?>">id: <?php echo "{$ad['ad_id']}"; ?>, title: <?php echo "{$ad['title']}"; ?></a>
    <br>
<?php
}
?>
</body>
</html>