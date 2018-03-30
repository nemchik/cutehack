<?php
$result="";
if ($member_db[1] != 1) {
    msg("error", "Access Denied", "You don't have permission to edit categories");
}

// ********************************************************************************

// Add Category
// ********************************************************************************

if ($action == "add") {
    $cat_name = htmlspecialchars(stripslashes($cat_name));
    if (!$cat_name) {
        msg("error", "Error !!!", "Please enter name of the category", "javascript:history.go(-1)");
    }
    $cat_icon = preg_replace("/ /", "", $cat_icon);
    if ($cat_icon == "(optional)") {
        $cat_icon = "";
    }

    $big_num = file("./data/cat.num.php");
    $big_num = $big_num[0];
    if (!$big_num or $big_num == "") {
        $big_num = 1;
    }

    $all_cats = file("./data/category.db.php");
    foreach ($all_cats as $null => $cat_line) {
        if (eregi("<\?", $cat_line)) {
            continue;
        }
        $cat_arr = explode("|", $cat_line);
        if ($cat_arr[1] == $cat_name) {
            msg("error", "Error !!!", "Category with this name already exist", "?mod=categories");
        }
        if ($cat_arr[0] == $big_num) {
            $big_num = 33;
        }
    }
    $new_cats = fopen("./data/category.db.php", "a");
    $cat_name = stripslashes(preg_replace(array("'\|'",), array("&#124",), $cat_name));
    fwrite($new_cats, "$big_num|$cat_name|$cat_icon|$cat_level||\n");
    fclose($new_cats);
    $big_num ++;

    $num_file = fopen("./data/cat.num.php", "w");
    fwrite($num_file, $big_num);
    fclose($num_file);
}
// ********************************************************************************

// Remove Category
// ********************************************************************************

elseif ($action == "remove") {
    if (!$catid) {
        msg("error", "Error !!!", "No category ID", "$PHP_SELF?mod=categories");
    }
    if ($catid == 1) {
        msg("error", "Error !!!", "You can not delete this category!", "$PHP_SELF?mod=categories");
    }

    $old_cats = file("./data/category.db.php");
    $new_cats = fopen("./data/category.db.php", "w");
    fwrite($new_cats, "<?PHP die(\"You don't have access to open this file !!!\"); ?>\n");

    foreach ($old_cats as $null => $old_cats_line) {
        if (eregi("<\?", $old_cats_line)) {
            continue;
        }
        $cat_arr = explode("|", $old_cats_line);
        if ($cat_arr[0] != $catid) {
            fwrite($new_cats, $old_cats_line);
        }
    }
    fclose($new_cats);
}
// ********************************************************************************

// Edit Category
// ********************************************************************************

elseif ($action == "edit") {
    if (!$catid) {
        msg("error", "Error !!!", "No category ID", "$PHP_SELF?mod=categories");
    }

    $all_cats = file("./data/category.db.php");
    foreach ($all_cats as $null => $cat_line) {
        if (eregi("<\?", $cat_line)) {
            continue;
        }
        $cat_arr = explode("|", $cat_line);
        if ($cat_arr[0] == $catid) {
            $cat_l1 = "";
            $cat_l2 = "";
            $cat_l3 = "";
            if ($cat_arr[3] == 1) {
                $cat_l1 = "selected";
            } elseif ($cat_arr[3] == 2) {
                $cat_l2 = "selected";
            } else {
                $cat_l3 = "selected";
            }
            $msg .= "<form action=$PHP_SELF?mod=categories method=post>
<table border=\"0\" width=\"421\" >
<tr>
<td width=\"64\" >Name</td>
<td width=\"341\" ><input value=\"$cat_arr[1]\" type=text name=cat_name></td>
</tr>
<tr>
<td width=\"64\" >Icon</td>
<td width=\"341\" ><input value=\"$cat_arr[2]\" type=text name=cat_icon></td>
</tr>
<tr>
<td width=\"64\" >Access Level</td>
<td width=\"341\">
<select name=cat_level>
<option value=\"1\" $cat_l1>Administrator</option>
<option value=\"2\" $cat_l2>Editor</option>
<option value=\"3\" $cat_l3>Journalist</option>
</select>
</tr>
<tr>
<td width=\"64\" ></td>
<td width=\"341\" ><input type=submit value=\"Save Changes\"</td>
</tr>
</table>
<input type=hidden name=action value=doedit>
<input type=hidden name=catid value=$catid>
</form>
";

            msg("options", "Edit Category", $msg);
        }
    }
}
// ********************************************************************************

// DO Edit Category
// ********************************************************************************

elseif ($action == "doedit") {
    $cat_name = htmlspecialchars(stripslashes($cat_name));
    if (!$catid) {
        msg("error", "Error !!!", "No category ID", "$PHP_SELF?mod=categories");
    }
    if ($cat_name == "") {
        msg("error", "Error !!!", "Category name can not be blank", "javascript:history.go(-1)");
    }

    $old_cats = file("./data/category.db.php");
    $new_cats = fopen("./data/category.db.php", "w");
    fwrite($new_cats, "<?PHP die(\"You don't have access to open this file !!!\"); ?>\n");
    foreach ($old_cats as $null => $cat_line) {
        if (eregi("<\?", $cat_line)) {
            continue;
        }
        $cat_arr = explode("|", $cat_line);
        if ($cat_arr[0] == $catid) {
            fwrite($new_cats, "$catid|$cat_name|$cat_icon|$cat_level||\n");
        } else {
            fwrite($new_cats, "$cat_line");
        }
    }
    fclose($new_cats);
}
// ********************************************************************************

// List all Categories
// ********************************************************************************

echoheader("options", "Categories");
echo<<<HTML

<table border=0 cellpadding=0 cellspacing=0 width="645" >
<form method=post action="$PHP_SELF">
<td width=321 height="33">
<b>Add Category</b>
<table border=0 cellpadding=0 cellspacing=0 width=300 class="panel" >
<tr>
<td width=98 height="25">
&nbsp;Name
<td width=206 height="25">
<input type=text name=cat_name>
</tr>
<tr>
<td width=98 height="22">
&nbsp;Icon URL
<td width=206 height="22">
<input onFocus="this.select()" value="(optional)" type=text name=cat_icon>
</tr>
<tr>
<td width=98 height="22">
&nbsp;Access Level
<td width=206 height="22">
<select name=cat_level>
<option value="1">Administrator</option>
<option value="2">Editor</option>
<option value="3" selected>Journalist</option>
</select>
</td>
</tr>
<tr>
<td width=98 height="32">
&nbsp;
<td width=206 height="32">
<input type=submit value=" Add Category ">
<input type=hidden name=mod value=categories>
<input type=hidden name=action value=add>
</tr>
</form>
</table>


<td width=320 height="33" align="center">
<!-- HELP -->
<table height="25" cellspacing="0" cellpadding="0">
<tr>
<td width="25" align=middle><img border="0" src="skins/images/help_small.gif" /></td>
<td >&nbsp;<a onClick="javascript:Help('categories')" href="#">What are categories and<br />
&nbsp;How to use them</a></td>
</tr>
</table><br />
<!-- END HELP -->

<tr>
<td width=654 colspan="2" height="11">
<img height=20 border=0 src="skins/images/blank.gif" width=1 />
</tr>
HTML;


$all_cats = file("./data/category.db.php");
$count_categories = 0;
foreach ($all_cats as $null => $cat_line) {
    if (eregi("<\?", $cat_line)) {
        continue;
    }
    if ($i%2 != 0) {
        $bg_NOT_IN_USE = "class=altern1";
    } else {
        $bg = "class=altern2";
    }
    $i++;
    $cat_arr = explode("|", $cat_line);
    $cat_arr[1] = stripslashes(preg_replace(array("'\"'", "'\''"), array("&quot;", "'"), $cat_arr[1]));
    $cat_help_names[] = $cat_arr[1];
    $cat_help_ids[] = $cat_arr[0];
    $result .= "

<tr>
<td $bg >&nbsp;<b>$cat_arr[0]</b></td>
<td $bg >$cat_arr[1]</td>
<td $bg >";
    if ($cat_arr[2] != "") {
        $result .= "<img border=0 src=\"$cat_arr[2]\" high=40 width=40 alt=\"$cat_arr[2]\" />";
    } else {
        $result .= "---";
    }
    $result .= "</td>
<td $bg >$cat_arr[3]</td>
<td $bg ><a href=\"$PHP_SELF?mod=categories&action=edit&catid=$cat_arr[0]\">[edit]</a> <a href=\"$PHP_SELF?mod=categories&action=remove&catid=$cat_arr[0]\">[delete]</a></td>
</tr>";
    $count_categories ++;
}

if ($count_categories == 0) {
    echo"<tr>
<td width=654 colspan=2 height=14>
<p align=center><br /><b>You havn't defined any categories yet</b><br />
categories are optional and you can write your news without having categories<br />
</tr>
<tr>";
} else {
    echo"<tr>
<td width=654 colspan=2 height=14>
<b>Categories</b>
</tr>
<tr>
<td width=654 colspan=2 height=1>
<table width=100% height=100% cellspacing=0 cellpadding=0>
<tr>
<td width=5% class=altern1>&nbsp;<u>ID</u></td>
<td width=35% class=altern1><u>Name</u></td>
<td width=20% class=altern1><u>Icon</u></td>
<td width=20% class=altern1><u>Access Level</u></td>
<td width=20% class=altern1><u>Action</u></td>
</tr>";

    echo $result;

    echo"</table>";
}

echo"
</table>";
echofooter();
