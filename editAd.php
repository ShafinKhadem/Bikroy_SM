<!DOCTYPE html>
<html>
<head>
    <title>Edit ad Demo</title>
    <link rel="stylesheet" href="bootstrap-4.2.1-dist/css/bootstrap.css">
</head>
<body>
    <h3>Check edit access function:</h3><p><pre style="background-color: grey; color: yellow">
    CREATE OR REPLACE FUNCTION "public"."check_edit_access"("_ad_id" int4, "usermail" varchar)
      RETURNS "pg_catalog"."bool" AS $BODY$
    declare
        cnt int;
    begin
        if is_admin(usermail) then
            return 't';
        end if;
        select "count"(*) into cnt from ads where ad_id=_ad_id and poster_mail=usermail;
        if cnt=0 then
            return 'f';
        else
            return 't';
        end if;
    end; $BODY$
      LANGUAGE plpgsql VOLATILE
      COST 100</pre></p>
    <h3>Edit ad function:</h3><p><pre style="background-color: grey; color: yellow">
    CREATE OR REPLACE FUNCTION "public"."edit_ad"("_ad" "public"."ads")
      RETURNS "pg_catalog"."int4" AS $BODY$
    declare
        cnt int;
    begin
        update ads set(ad_id, buy_or_sell, poster_phone, price, is_negotiable, title, details, category, subcategory, "location", sublocation, poster_mail, approver_mail, "time", "date") =
        (_ad.ad_id, _ad.buy_or_sell, _ad.poster_phone, _ad.price, _ad.is_negotiable, _ad.title, _ad.details, _ad.category, _ad.subcategory, _ad."location", _ad.sublocation, _ad.poster_mail, _ad.approver_mail, _ad."time", _ad."date") where ad_id=_ad.ad_id;
        GET DIAGNOSTICS cnt = ROW_COUNT;
        return cnt;
    end; $BODY$
      LANGUAGE plpgsql VOLATILE
      COST 100</pre></p>
    <h3>Edit mobile ad function:</h3><p><pre style="background-color: grey; color: yellow">
    CREATE OR REPLACE FUNCTION "public"."edit_mobile_ad"("_mobile_ad" "public"."mobile_ads_view")
      RETURNS "pg_catalog"."int4" AS $BODY$
    declare
        cnt int;
    begin
        perform edit_ad(row(_mobile_ad.ad_id, _mobile_ad.buy_or_sell, _mobile_ad.poster_phone, _mobile_ad.price, _mobile_ad.is_negotiable, _mobile_ad.title, _mobile_ad.details, _mobile_ad.category, _mobile_ad.subcategory, _mobile_ad."location", _mobile_ad.sublocation, _mobile_ad.poster_mail, _mobile_ad.approver_mail, _mobile_ad."time", _mobile_ad."date"));
        update mobile_ads set(brand, model, edition, features, authenticity, "condition") =
        (_mobile_ad.brand, _mobile_ad.model, _mobile_ad.edition, _mobile_ad.features, _mobile_ad.authenticity, _mobile_ad.condition) where ad_id=_mobile_ad.ad_id;
        GET DIAGNOSTICS cnt = ROW_COUNT;
        return cnt;
    end; $BODY$
      LANGUAGE plpgsql VOLATILE
      COST 100</pre></p>
<?php

require 'vendor/autoload.php';

use BikroySM\Connection as Connection;

session_start();

try {
    $epdo = Connection::get()->connect();
    $str = "check_edit_access({$_GET['adid']}, '{$_SESSION['email']}')";
    if (!$epdo->getFromWhereVal($str)) {
        echo "U r neither admin nor poster of this ad. How dare u try to edit this ad 😠";
        exit();
    }
    $str = "select * from ads v where v.ad_id={$_GET['adid']};";
    $str2 = 'edit_ad';
    $ad = $epdo->getQueryResults($str)[0];
    if ($ad['category']=='electronics') {
        $str = "select * from electronics_ads_view v where v.ad_id={$_GET['adid']};";
        $str2 = "edit_electronics_ad";
    } elseif ($ad['subcategory']=='car') {
        $str = "select * from car_ads_view v where v.ad_id={$_GET['adid']};";
        $str2 = "edit_car_ad";
    } elseif ($ad['subcategory']=='motor_cycle') {
        $str = "select * from motor_cycle_ads_view v where v.ad_id={$_GET['adid']};";
        $str2 = "edit_motor_cycle_ad";
    } elseif ($ad['subcategory']=='mobile_phone') {
        $str = "select * from mobile_ads_view v where v.ad_id={$_GET['adid']};";
        $str2 = "edit_mobile_ad";
    }
    $ad = $epdo->getQueryResults($str)[0];
    if (isset($_POST['saveEdit'])) {
        $param = 'row(';
        foreach ($ad as $key => $value) {
            if ($param!='row(') $param = $param.', ';
            if (empty($_POST[$key])) $param = $param.'null';
            else $param = $param.'\''.str_replace("'", "''", $_POST[$key]).'\'';
        }
        $param = $param.')';
        // echo $param;
        if (isset($str2)) {
            $epdo->getFromWhere("{$str2}({$param})");
        }
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
    if ($key=='ad_id'or $key=='category'or $key=='subcategory' or $key=='poster_mail' or $key=='approver_mail' or $key=='time' or $key=='date') {
        if (is_bool($value)) var_export($value); else echo "{$value}";  // u can't access labels via $_POST
?>
        <input type="hidden" name="<?php echo($key); ?>" value="<?php if (is_bool($value)) var_export($value); else echo "{$value}"; ?>">
<?php
    }
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