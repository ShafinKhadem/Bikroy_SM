<?php
require 'vendor/autoload.php';

use BikroySM\Connection as Connection;

try {
    $epdo = Connection::get()->connect();

    // the next section of this try block just acts as server for ajax.
    if (isset($_POST['postAd'])) {  // eita na dile tui form submit korte parbi na.
        goto skipTry;
    }
    if (isset($_REQUEST['loc'])) {  // loc er jaygay location use korle always dhora khabe
            $sublocations = $epdo->getFromWhereCol("get_sublocations('{$_POST['loc']}')");
            foreach ($sublocations as $sublocation) {
        ?>
                <input type="radio" name="sublocation" value="<?php echo($sublocation); ?>" <?php if(isset($_POST['sublocation']) and $_POST['sublocation']==$sublocation)  echo 'checked="checked"';?> ><?php echo $sublocation; ?>
        <?php
            }
        exit();
    }
    if (isset($_POST['subcategory'])) { // eitake oboshyoi porertar upore dite hobe, cause eitate category o thakbe.
        if ($_POST['category']=='others') {
            exit();
        }
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
        exit();
    }
    if (isset($_POST['category'])) {
        $subcategories = $epdo->getFromWhereCol("get_subcategories('{$_POST['category']}')");
        foreach ($subcategories as $subcategory) {
    ?>
            <input type="radio" name="subcategory" onclick="getAttrs(this)" value="<?php echo($subcategory); ?>" <?php if(isset($_POST['subcategory']) and $_POST['subcategory']==$subcategory)  echo 'checked="checked"';?> ><?php echo $subcategory; ?>
    <?php
        }
        exit();
    }
    skipTry: ;
} catch (\PDOException $e) {
    echo $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Post ad Demo</title>
    <link rel="stylesheet" href="bootstrap-4.2.1-dist/css/bootstrap.css">
</head>
<body>
    <h3>Post ad function:</h3><p><pre style="background-color: grey; color: yellow">
    CREATE OR REPLACE FUNCTION "public"."post_ad"("_buy_or_sell" bool, "_poster_phone" varchar, "_price" int4, "_is_negotiable" bool, "_title" varchar, "_details" varchar, "_category" varchar, "_subcategory" varchar, "_location" varchar, "_sublocation" varchar, "_poster_mail" varchar)
      RETURNS "pg_catalog"."int4" AS $BODY$
    declare
        adid int;
        cnt int;
        _approver_mail varchar;
    begin
        if is_admin(_poster_mail) then
            _approver_mail:=_poster_mail;
        else
            _approver_mail:=NULL;
        end if;
        insert into ads(buy_or_sell, poster_phone, price, is_negotiable, title, details, category, subcategory, "location", sublocation, poster_mail, approver_mail) values(_buy_or_sell, _poster_phone, _price, _is_negotiable, _title, _details, _category, _subcategory, _location, _sublocation, _poster_mail, _approver_mail) returning ad_id into adid;
        GET DIAGNOSTICS cnt = ROW_COUNT;
        return adid;
    end; $BODY$
      LANGUAGE plpgsql VOLATILE
      COST 100</pre></p>

    <h3>Post ad trigger:</h3><p><pre style="background-color: grey; color: yellow">
    CREATE OR REPLACE FUNCTION "public"."post_trigger"()
      RETURNS "pg_catalog"."trigger" AS $BODY$
    DECLARE
        msg varchar;
        price int;
    BEGIN
        select ad_price into price from product_type where category=new.category and subcategory=new.subcategory;
        msg='ur ad is pending for admin approval, u need to first pay '||price||'. ur ad id is '||new.ad_id;
        perform send_message('bikroy.com', new.poster_mail, msg);
        return new;
    END
    $BODY$
      LANGUAGE plpgsql VOLATILE
      COST 100</pre></p>

<?php

session_start();

$target_file = "";

function uploadImage() {
    global $target_file;

    $target_dir = "tmp/";
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if($check !== false) {
        echo "File is an image - " . $check["mime"] . ".";
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }
    // Check if file already exists
    if (file_exists($target_file)) {
        echo "Sorry, invalid filename. A file with same name has already been uploaded.";
        $uploadOk = 0;
    }
    // Check file size is <= 500 KB
    if ($_FILES["fileToUpload"]["size"] > 500000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }
    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        echo "Sorry, only JPG, JPEG, PNG files are allowed.";
        $uploadOk = 0;
    }
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk != 0) {
    // if everything is ok, try to upload file
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
        } else {
            echo "Sorry, there was an error uploading your file.";
            $uploadOk = 0;
        }
    }

    if ($uploadOk == 0) {
        exit();
    }
}

if (empty($_SESSION['email'])) {
    header('location: signin.php');
} else {
    echo nl2br("Email: ".$_SESSION['email']."\n"."Name: ".$_SESSION['name']."\n");
}
try {

    if (isset($_POST['postAd'])) {
        uploadImage();
        $tit = str_replace("'", "'__'", $_POST['title']);
        $det = str_replace("'", "'__'", $_POST['details']);
        $str = "post_ad('{$_POST['buy_or_sell']}', '{$_POST['poster_phone']}', '{$_POST['price']}', '{$_POST['is_negotiable']}', '{$tit}'
        , '{$det}', '{$_POST['category']}', '{$_POST['subcategory']}', '{$_POST['location']}', '{$_POST['sublocation']}', '{$_SESSION['email']}')";
        $str = str_replace("''", "null", $str);
        $str = str_replace("'__'", "''", $str);
        echo $str;
        $adid = $epdo->getFromWhereVal($str);
        rename($target_file, "uploads/{$adid}.png");
        if ($_POST['category']=='electronics') {
            $str = "insert into electronics_ads(ad_id, brand, model) values({$adid}, '{$_POST['brand']}', '{$_POST['model']}')";
        } elseif ($_POST['subcategory']=='car') {
            $str = "insert into car_ads(ad_id, brand, model, edition, model_year, \"condition\", transmission, body_type, fuel_type, engine_capacity, kilometers_run) values
            ({$adid}, '{$_POST['brand']}', '{$_POST['model']}', '{$_POST['edition']}', '{$_POST['model_year']}', '{$_POST['condition']}'
            , '{$_POST['transmission']}', '{$_POST['body_type']}', '{$_POST['fuel_type']}', '{$_POST['engine_capacity']}', '{$_POST['kilometers_run']}')";
        } elseif ($_POST['subcategory']=='motor_cycle') {
            $str = "insert into motor_cycle_ads(ad_id, bike_type, brand, model, model_year, \"condition\", engine_capacity, kilometers_run) values
            ({$adid}, '{$_POST['bike_type']}', '{$_POST['brand']}', '{$_POST['model']}', '{$_POST['model_year']}', '{$_POST['condition']}'
            , '{$_POST['engine_capacity']}', '{$_POST['kilometers_run']}')";
        } elseif ($_POST['subcategory']=='mobile_phone') {
            $str = "insert into mobile_phone_ads(ad_id, brand, model, edition, features, authenticity, \"condition\") values
            ({$adid}, '{$_POST['brand']}', '{$_POST['model']}', '{$_POST['edition']}', '{$_POST['features']}', '{$_POST['authenticity']}', '{$_POST['condition']}')";
        }
        if ($_POST['category']!='others') {
            $str = str_replace("''", "null", $str);
            echo $str;
            $epdo->getQueryResults($str);
        }
        header('location: user.php');
    }

} catch (\PDOException $e) {
    echo $e->getMessage();
}

?>

    <h1><center>Posting ad form</center></h1><br><br>

    <form method="post" enctype="multipart/form-data">
        Select image to upload (must be in png, jpg or jpeg format and size must not exceed 500 KB):
        <br><br><label class="btn btn-warning">
            Browse <input type="file" name="fileToUpload" id="fileToUpload">
        </label><br><br>

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
        <textarea id="details" name="details"  rows="10" cols="100"><?php if(isset($_POST['details'])) echo htmlentities ($_POST['details']); ?></textarea><br>

        <br><br>
<script>
    function ajax(caller, attr, trgt) {
        // alert(attr+'='+caller.value);
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById(trgt).innerHTML = this.responseText;
            }
        };

        xhttp.open('POST', 'postAd.php', true);
        xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhttp.send(attr+'='+caller.value);



        // just for personal use
        if (attr=='category') {document.getElementById('attrs').innerHTML='';}
    }

    function getAttrs(caller) {
        var radios = document.getElementsByName('category');
        for (var i = 0, length = radios.length; i < length; i++) {
            if (radios[i].checked) {
                var str = 'subcategory='+caller.value+'&category='+radios[i].value;
                break;
            }
        }
        // alert(str);
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById('attrs').innerHTML = this.responseText;
            }
        };

        xhttp.open('POST', 'postAd.php', true);
        xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhttp.send(str);
    }
</script>
        location:
<?php
$locations = $epdo->getFromWhereCol('get_locations()');
foreach ($locations as $location) {
?>
    <input type="radio" name="location" onclick="ajax(this, 'loc', 'sublocs')" value="<?php echo($location); ?>" <?php if(isset($_POST['location']) and $_POST['location']==$location)  echo 'checked="checked"';?> ><?php echo $location; ?>
<?php
}
?>

        <br>

        sublocation:
        <span id="sublocs"></span>

        <br><br>

        category:
<?php
$categories = $epdo->getFromWhereCol('get_categories()');
foreach ($categories as $category) {
?>
    <input type="radio" name="category" onclick="ajax(this, 'category', 'subcats')" value="<?php echo($category); ?>" <?php if(isset($_POST['category']) and $_POST['category']==$category)  echo 'checked="checked"';?> ><?php echo $category; ?>
<?php
}
?>

        <br>

        subcategory:
        <span id="subcats"></span>
        <div id="attrs"></div>

        <br><br>
        <input type="submit" class="btn btn-info" name="postAd" value="postAd">
    </form>

</body>
</html>