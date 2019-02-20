<!DOCTYPE html>
<html>
<head>
    <title>Show ad Demo</title>
    <link rel="stylesheet" href="bootstrap-4.2.1-dist/css/bootstrap.css">
</head>
<body>

<a href="index.php">Home page</a><br><br>

<?php

require 'vendor/autoload.php';

use BikroySM\Connection as Connection;

function showAds($ads) {
?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ad_id</th>
                <th>image</th>
                <th>title</th>
                <th>price</th>
                <th>posting time</th>
                <th>category</th>
                <th>subcategory</th>
                <th>location</th>
                <th>sublocation</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ads as $ad) : ?>
                <tr>
                    <td><a href="showAd.php?adid=<?php echo($ad['ad_id']); ?>"><?php echo "{$ad['ad_id']}"; ?></a></td>
                    <td><img src="<?php echo("uploads/{$ad['ad_id']}.png"); ?>" height="80" width="80">
                    <td><?php echo $ad['title']; ?></td>
                    <td><?php echo $ad['price']; ?></td>
                    <td><?php echo $ad['time']; ?></td>
                    <td><?php echo $ad['category']; ?></td>
                    <td><?php echo $ad['subcategory']; ?></td>
                    <td><?php echo $ad['location']; ?></td>
                    <td><?php echo $ad['sublocation']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php
}

try {
    $epdo = Connection::get()->connect();
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
        <option value="time desc" <?php if (isset($_POST['sort']) and $_POST['sort']=="time desc") { ?>selected="true" <?php }; ?> >Time: newest on top</option>
        <option value="time asc" <?php if (isset($_POST['sort']) and $_POST['sort']=="time asc") { ?>selected="true" <?php }; ?> >Time: oldest on top</option>
        <option value="price asc" <?php if (isset($_POST['sort']) and $_POST['sort']=="price asc") { ?>selected="true" <?php }; ?> >Price: low to high</option>
        <option value="price desc" <?php if (isset($_POST['sort']) and $_POST['sort']=="price desc") { ?>selected="true" <?php }; ?> >Price: high to low</option>
    </select>

    <br><br>
    <h4>Filter results by:</h4>

    location:
    <div class="custom-control custom-control-inline custom-radio ml-3">
        <input type="radio" id="any" name="location" class="custom-control-input" value="any" <?php if(isset($_POST['location']) and $_POST['location']=='any')  echo 'checked="checked"';?> >
        <label class="custom-control-label" for="any">any</label>
    </div>
<?php
$locations = $epdo->getFromWhereCol('get_locations()');
foreach ($locations as $location) {
?>
    <div class="custom-control custom-control-inline custom-radio">
        <input type="radio" id="<?php echo($location); ?>" name="location" class="custom-control-input" value="<?php echo($location); ?>" <?php if(isset($_POST['location']) and $_POST['location']==$location)  echo 'checked="checked"';?> >
        <label class="custom-control-label" for="<?php echo($location); ?>"><?php echo $location; ?></label>
    </div>
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

<br>

<div class="container">
    <p style="background-color: grey; color: yellow"><?php echo "select * from ".$str2; ?></p><br>
    <center><h1><font color="blue">2 random top ads with given filters: 🙂</font></h1></center>
    <br><br>
    <?php showAds($topads); ?>

    <br><br><br>
    <p style="background-color: grey; color: yellow"><?php echo "select * from ".$str; ?></p>

    <center><h1><font color="blue">all ads with given filter & ordering: 🙂</font></h1></center>
    <br><br>
    <?php showAds($ads); ?>

</div>
</body>
</html>