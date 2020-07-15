<?php
////////////
// XFields, module for CuteNews (http://www.cutephp.com).
// Written by SMKiller2 (smk2@xs4all.nl)

if ($xfieldssubactionadd == "add") {
    $xfieldssubaction = $xfieldssubactionadd;
}

if (!isset($_SERVER)) {
    $_SERVER = $HTTP_SERVER_VARS;
}
if (!isset($rowstyle1)) {
    $rowstyleodd = "class=altern1 style=\"padding: 4px;\"";
}
if (!isset($rowstyle2)) {
    $rowstyleeven = "class=altern2 style=\"padding: 4px;\"";
}


if ($xf_inited !== true) { // Prevent "Cannot redeclare" error
    ////////////
    // Make the text safe for html output
    function safehtml($text)
    {
        return htmlentities($text);
    }

    ////////////
    // Save XFields to a file, used when you modify it in the Options section.
    function xfieldssave($data)
    {
        $data = array_values($data);
        foreach ($data as $index => $value) {
            if (eregi("<\?", $value)) {
                continue;
            }
            $value = array_values($value);
            foreach ($value as $index2 => $value2) {
                if (eregi("<\?", $value2)) {
                    continue;
                }
                $value2 = stripslashes($value2);
                $value2 = str_replace("|", "&#124;", $value2);
                $value2 = str_replace("\r\n", "__NEWL__", $value2);
                $filecontents .= $value2 . ($index2 < count($value) - 1 ? "|" : "");
            }
            $filecontents .= ($index < count($data) - 1 ? "\r\n" : "");
        }

        $filehandle = fopen("./data/xfields.db.php", "w+");
        if (!$filehandle) {
            msg("error", "XFields Error", "Could not save data to file \"./data/xfields.db.php\", check if the file exists and is properly chmoded.");
        }
        fwrite($filehandle, "<?PHP die(\"You don't have access to open this file !!!\"); ?>\n");
        fwrite($filehandle, $filecontents);
        fclose($filehandle);
        header("Location: http://" . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"] .
        "?mod=xfields&xfieldsaction=configure");
        exit;
    }

    ////////////
    // Load XFields from a file, used when you do anything in the Options section.
    function xfieldsload()
    {
        $filecontents = file("{$GLOBALS["cutepath"]}/data/xfields.db.php");
        if (!is_array($filecontents)) {
            msg("error", "XFields Error", "Could not load data from file \"{$GLOBALS["cutepath"]}/data/xfields.db.php\", check if the file exists and is properly chmoded.");
        }

        foreach ($filecontents as $name => $value) {
            if (eregi("<\?", $value)) {
                continue;
            }
            $filecontents[$name] = explode("|", trim($value));
            foreach ($filecontents[$name] as $name2 => $value2) {
                $value2 = str_replace("&#124;", "|", $value2);
                $value2 = str_replace("__NEWL__", "\r\n", $value2);
                $filecontents[$name][$name2] = $value2;
            }
        }
        return $filecontents;
    }

    ////////////
    // Save XFields Data to a file, used when a news item is added/edited
    function xfieldsdatasave($data)
    {
        foreach ($data as $id => $xfieldsdata) {
            if (eregi("<\?", $xfieldsdata)) {
                continue;
            }
            foreach ($xfieldsdata as $xfielddataname => $xfielddatavalue) {
                if (eregi("<\?", $xfielddatavalue)) {
                    continue;
                }
                if ($xfielddatavalue == "") {
                    unset($xfieldsdata[$xfielddataname]);
                    continue;
                }
                $xfielddataname = stripslashes($xfielddataname);
                $xfielddatavalue = stripslashes($xfielddatavalue);
                $xfielddataname = str_replace("|", "&#124;", $xfielddataname);
                $xfielddataname = str_replace("\r\n", "__NEWL__", $xfielddataname);
                $xfielddatavalue = str_replace("|", "&#124;", $xfielddatavalue);
                $xfielddatavalue = str_replace("\r\n", "__NEWL__", $xfielddatavalue);
                $filecontents[$id][] = "$xfielddataname|$xfielddatavalue";
            }
            $filecontents[$id] = "$id|>|" . implode("||", $filecontents[$id]);
        }
        $filecontents = @implode("\r\n", $filecontents);

        $filehandle = fopen("./data/xfieldsdata.db.php", "w");

        if (!$filehandle) {
            msg("error", "XFields Error", "Could not save data to file \"./data/xfieldsdata.db.php\", check if the file exists and is properly chmoded.");
        }
        fwrite($filehandle, "<?PHP die(\"You don't have access to open this file !!!\"); ?>\n");
        fwrite($filehandle, $filecontents);
        fclose($filehandle);
    }
    ////////////
    // Load XFields Data from a file, used when a your news is displayed or when you edit a news item.
    function xfieldsdataload()
    {
        $filecontents = file("{$GLOBALS["cutepath"]}/data/xfieldsdata.db.php");
        if (!is_array($filecontents)) {
            msg("error", "XFields Error", "Could not load data from file \"{$GLOBALS["cutepath"]}/data/xfieldsdata.db.php\", check if the file exists and is properly chmoded.");
        }

        foreach ($filecontents as $name => $value) {
            if (eregi("<\?", $value)) {
                continue;
            }
            list($id, $xfieldsdata) = explode("|>|", trim($value), 2);
            $xfieldsdata = explode("||", $xfieldsdata);
            foreach ($xfieldsdata as $xfielddata) {
                if (eregi("<\?", $xfielddata)) {
                    continue;
                }
                list($xfielddataname, $xfielddatavalue) = explode("|", $xfielddata);
                $xfielddataname = str_replace("&#124;", "|", $xfielddataname);
                $xfielddataname = str_replace("__NEWL__", "\r\n", $xfielddataname);
                $xfielddatavalue = str_replace("&#124;", "|", $xfielddatavalue);
                $xfielddatavalue = str_replace("__NEWL__", "\r\n", $xfielddatavalue);
                $data[$id][$xfielddataname] = $xfielddatavalue;
            }
        }
        return $data;
    }
    ////////////
    // Make the HTML insertion code for the emoticons
    function xfSmilies($element_id, $columns = false)
    {
        global $config_http_script_dir, $config_smilies;

        $smilies = explode(",", $config_smilies);
        $output = "";
        foreach ($smilies as $index9 => $smily) {
            $i++;
            $smily = trim($smily);

            $output .= "<a href=\"javascript:xfInsertText(':$smily:', '$element_id')\"><img style=\"border: none;\" alt=\"$smily\" src=\"$config_http_script_dir/data/emoticons/$smily.gif\" /></a>";
            if ($columns and
          $i % $columns == 0) {
                $output .= "<br />";
            } else {
                $output .= "";
            }
        }
        return $output;
    }

    ////////////
    // Move an array item
    function array_move(&$array, $index1, $dist)
    {
        $index2 = $index1 + $dist;
        if ($index1 < 0 or
        $index1 > count($array) - 1 or
        $index2 < 0 or
        $index2 > count($array) - 1) {
            return false;
        }
        $value1 = $array[$index1];

        $array[$index1] = $array[$index2];
        $array[$index2] = $value1;

        return true;
    }

    $xf_inited = true;
}

$xfields = xfieldsload();
switch ($xfieldsaction) {
  case "configure":
    switch ($xfieldssubaction) {
      case "delete":
        if (!isset($xfieldsindex)) {
            msg("error", "XFields Error", "You should select an item to delete.<br /><a href=\"javascript:history.go(-1)\">go back</a>");
        }
        msg("options", "Delete XField", "
Are you sure you want to delete this XField? Please not that the data you entered will not be deleted.
<br />
<a href=\"$PHP_SELF?mod=xfields&amp;xfieldsaction=configure&amp;xfieldsindex=$xfieldsindex&amp;xfieldssubaction=delete2\">[Yes]</a>
<a href=\"$PHP_SELF?mod=xfields&amp;xfieldsaction=configure\">[No]</a>
");
        break;
      case "delete2":
        if (!isset($xfieldsindex)) {
            msg("error", "XFields Error", "You should select an item to delete.<br /><a href=\"javascript:history.go(-1)\">go back</a>");
        }
        unset($xfields[$xfieldsindex]);
        @xfieldssave($xfields);
        break;
      case "moveup":
        if (!isset($xfieldsindex)) {
            msg("error", "XFields Error", "You should select an item to move.<br /><a href=\"javascript:history.go(-1)\">go back</a>");
        }
        array_move($xfields, $xfieldsindex, -1);
        @xfieldssave($xfields);
        break;
      case "movedown":
        if (!isset($xfieldsindex)) {
            msg("error", "XFields Error", "You should select an item to move.<br /><a href=\"javascript:history.go(-1)\">go back</a>");
        }
        array_move($xfields, $xfieldsindex, -1);
        @xfieldssave($xfields);
        break;
      case "add":
        $xfieldsindex = count($xfields);
        // Fall trough to edit
        // no break
      case "edit":
        if (!isset($xfieldsindex)) {
            msg("error", "XFields Error", "You should select an item to edit.<br /><a href=\"javascript:history.go(-1)\">go back</a>");
        }

        if (!$editedxfield) {
            $editedxfield = $xfields[$xfieldsindex];
        } elseif (strlen(trim($editedxfield[0])) > 0 and
            strlen(trim($editedxfield[1])) > 0) {
            foreach ($xfields as $name => $value) {
                if ($name != $xfieldsindex and
                $value[0] == $editedxfield[0]) {
                    msg("error", "XFields Error", "The name must be unique.<br /><a href=\"javascript:history.go(-1)\">go back</a>");
                }
            }
            $editedxfield[0] = strtolower(trim($editedxfield[0]));
            if ($editedxfield[2] == "custom") {
                $editedxfield[2] = $editedxfield["2_custom"];
            }
            settype($editedxfield[2], "string");
            if ($editedxfield[3] == "select") {
                $options = array();
                foreach (explode("\r\n", $editedxfield["4_select"]) as $name => $value) {
                    $value = trim($value);
                    if (!in_array($value, $options)) {
                        $options[] = $value;
                    }
                }
                if (count($options) < 2) {
                    msg("error", "XFields Error", "If you have selected &quot;listbox&quot; as type, please fill in two or more options.<br /><a href=\"javascript:history.go(-1)\">go back</a>");
                }
                $editedxfield[4] = implode("\r\n", $options);
            } else {
                $editedxfield[4] = $editedxfield["4_{$editedxfield[3]}"];
            }
            unset($editedxfield["2_custom"], $editedxfield["4_text"], $editedxfield["4_textarea"], $editedxfield["4_select"]);
            if ($editedxfield[3] == "select") {
                $editedxfield[5] = 0;
            } else {
                $editedxfield[5] = ($editedxfield[5] == "on" ? 1 : 0);
            }
            ksort($editedxfield);

            $xfields[$xfieldsindex] = $editedxfield;
            ksort($xfields);
            @xfieldssave($xfields);
            break;
        } else {
            msg("error", "XFields Error", "You should fill in the name and description.<br /><a href=\"javascript:history.go(-1)\">go back</a>");
        }
        echoheader("options", (($xfieldssubaction == "add") ? "Add" : "Edit") . " XField");
        $checked = ($editedxfield[5] ? " checked" : "");
?>
    <form action="<?=safehtml($_SERVER["PHP_SELF"])?>" method="post" name="xfieldsform">
      <script language="javascript">
      function ShowOrHideEx(id, show) {
        var item = null;
        if (document.getElementById) {
          item = document.getElementById(id);
        } else if (document.all) {
          item = document.all[id];
        } else if (document.layers){
          item = document.layers[id];
        }
        if (item && item.style) {
          item.style.display = show ? "" : "none";
        }
      }
      function onTypeChange(value) {
        ShowOrHideEx("default_text", value == "text");
        ShowOrHideEx("default_textarea", value == "textarea");
        ShowOrHideEx("select_options", value == "select");
        ShowOrHideEx("optional", value != "select");
      }
      function onCategoryChange(value) {
        ShowOrHideEx("category_custom", value == "custom");
      }
      </script>
      <input type="hidden" name="mod" value="xfields">
      <input type="hidden" name="xfieldsaction" value="configure">
      <input type="hidden" name="xfieldssubaction" value="edit">
      <input type="hidden" name="xfieldsindex" value="<?=$xfieldsindex?>">
      <p>
        <strong>Name:</strong>
        <br />
          <input style="width: 400px;" type="text" name="editedxfield[0]" value="<?=safehtml($editedxfield[0])?>" />
        <br />
        (Enter the name of the field)
      </p>
      <p>
      <strong>Description:</strong>
      <br />
        <input style="width: 400px;" type="text" name="editedxfield[1]" value="<?=safehtml($editedxfield[1])?>" />
        <br />
        (Enter the name that will be showed when editing the field)
      </p>
<?php
        $all_cats = file("./data/category.db.php");
        $cat_options = "";
        $cat_selected = false;
        foreach ($all_cats as $cat_index => $cat_line) {
            if (eregi("<\?", $cat_line)) {
                continue;
            }
            $cat_arr = explode("|", $cat_line);
            $cat_arr[1] = stripslashes(preg_replace(array("'\"'", "'\''"), array("&quot;", "&#039;"), $cat_arr[1]));
            if ($cat_arr[0] == $editedxfield[2]) {
                $cat_selected = true;
                $cat_options .= "<option value=\"$cat_arr[0]\" selected>$cat_arr[1]</option>";
            } else {
                $cat_options .= "<option value=\"$cat_arr[0]\">$cat_arr[1]</option>";
            }
        }
        if ($cat_options != "") {
            ?>
      <p>
        <strong>Category:</strong>
          <br />
          <select style="width: 400px; border: 0;" name="editedxfield[2]" id="category" onchange="onCategoryChange(this.value)">
            <option value=""<?=($editedxfield[2] == "") ? " selected" : ""?>>All</option>
            <?=$cat_options?>
            <option value="custom"<?=($editedxfield[2] != "" and !$cat_selected) ? " selected" : ""?>>Custom...</options>
          </select>
        <br />
        <span id="category_custom" <?=($editedxfield[2] == "" or $cat_selected) ? " style=\"display: none;\"" : ""?>>
          <input type="text" style="width: 400px;" name="editedxfield[2_custom]" value="<?=safehtml($editedxfield[2])?>" />
          <a onclick="javascript:Help('XFields Custom Category')" href="#">[?]</a>
          <br />
        </span>
        (The category where the XField should appear. Custom for multiple)
        <noscript>
          <br />
          Warning: You should have JavaScript enabled to use the custom feature.
        </noscript>
      </p>
<?php
        }
?>
      <p>
        <strong>Type:</strong>
        <br />
        <select style="width: 400px;" name="editedxfield[3]" id="type" onchange="onTypeChange(this.value)" />
          <option value="text"<?=($editedxfield[3] != "textarea") ? " selected" : ""?>>Single Line</option>
          <option value="textarea"<?=($editedxfield[3] == "textarea") ? " selected" : ""?>>Multi Line</option>
          <option value="select"<?=($editedxfield[3] == "select") ? " selected" : ""?>>Dropdown Listbox</option>
        </select>
        <br />
        (The type of the XField)
      </p>
      <p id="default_text">
        <strong>Default:</strong>
        <br />
        <input style="width: 400px;" type="text" name="editedxfield[4_text]" value="<?=($editedxfield[3] == "text") ? safehtml($editedxfield[4]) : ""?>" />
        <br />
        (Enter the default value for this XField, this field is optional)
      </p>
      <p id="default_textarea">
        <strong>Default:</strong>
        <br />
        <textarea style="width: 400px; height: 150px;" name="editedxfield[4_textarea]"><?=($editedxfield[3] == "textarea") ? safehtml($editedxfield[4]) : ""?></textarea>
        <br />
        (Enter the default value for this XField, this field is optional)
      </p>
      <p id="select_options">
        <strong>Options:</strong>
        <br />
        <textarea style="width: 400px; height: 100px;" name="editedxfield[4_select]"><?=($editedxfield[3] == "select") ? safehtml($editedxfield[4]) : ""?></textarea>
        <br />
        (Enter the options availible for this listbox)
      </p>
      <p id="optional">
        <strong>Optional:</strong>
        <br />
        <span style="width: 400px;">
          <input type="checkbox" class=checkbox name="editedxfield[5]"<?=$checked?> />
        </span>
        <br />
        (Check if this field is optional)
      </p>
      <p>
        <input style="width: 100px;" type="submit" accesskey="s" value="Save changes" />
      </p>
    </form>
    <script type="text/javascript">
    <!--
      var item_type = null;
      var item_category = null;
      if (document.getElementById) {
        item_type = document.getElementById("type");
        item_category = document.getElementById("category");
      } else if (document.all) {
        item_type = document.all["type"];
        item_category = document.all["category"];
      } else if (document.layers) {
        item_type = document.layers["type"];
        item_category = document.layers["category"];
      }
      if (item_type) {
        onTypeChange(item_type.value);
        onCategoryChange(item_category.value);
      }
    // -->
    </script>
<?php
        echofooter();
        break;
// Sort by XField v1.0 - addblock
  case 'noop':
    break;
// Sort by XField v1.0 - End addblock
      default:
        echoheader("options", "Configure XFields");
?>
    <form action="<?=safehtml($_SERVER["PHP_SELF"])?>" method="post" name="xfieldsform">
      <input type="hidden" name="mod" value="xfields">
      <input type="hidden" name="xfieldsaction" value="configure">
      <input type="hidden" name="xfieldssubactionadd" value="">
      <table border="0" cellspacing="0" cellpadding="2">
        <tr>
          <td style="text-align: right; padding-bottom: 10px;" colspan="7">
            <img src="skins/images/help_small.gif" style="width: 25px; height: 25px; border: 0; vertical-align: middle; float: right; margin-left: 5px;" />
            <a href="#" onclick="javascript:Help('Understanding XFields')">Understanding XFields</a><br />
            <a href="#" onclick="javascript:Help('Configuring XFields')">Configuring XFields</a>
          </td>
        </tr>
        <tr>
          <th style="width: 240px; text-align: left;">Name</th>
          <th style="width: 140px; text-align: left;">Category</th>
          <th style="width: 120px; text-align: left;">Type</th>
          <th style="width: 80px; text-align: left;">Optional</th>
          <th>&nbsp;</th>
        </tr>
<?php
        if (count($xfields) == 0) {
            echo "<tr><td colspan=\"5\">There are no XFields configured</td></tr>";
        } else {
            foreach ($xfields as $name => $value) {
                if (eregi("<\?", $value)) {
                    continue;
                }
                $rowstyle = ($name % 2) ? $rowstyleodd : $rowstyleeven; ?>
        <tr>
          <td <?=$rowstyle?>>
            <?=safehtml($value[0])?>
          </td>
          <td <?=$rowstyle?>>
            <?=(trim($value[2]) ? safehtml($value[2]) : "All")?>
          </td>
          <td <?=$rowstyle?>>
            <?=(($value[3] == "text") ? "Single Line" : "")?>
            <?=(($value[3] == "textarea") ? "Multi Line" : "")?>
            <?=(($value[3] == "select") ? "Dropdown Listbox" : "")?>
          </td>
          <td <?=$rowstyle?>>
            <?=($value[5] != 0 ? "Yes" : "No")?>
          </td>
          <td <?=$rowstyle?>>
            <input type="radio" name="xfieldsindex" value="<?=$name?>">
          </td>
        </tr>
<?php
            }
        }
?>
      <tr>
        <td colspan="6" style="text-align: right; padding-top: 10px;">
          <?php if (count($xfields) > 0) {
    ?>
          Action:
          <select name="xfieldssubaction">
            <option value="edit">Edit</option>
            <option value="delete">Delete</option>
            <option value="moveup">Move Up</option>
            <option value="movedown">Move Down</option>
          </select>
          <input type="submit" value="Go" onclick="document.forms['xfieldsform'].xfieldssubactionadd.value = '';">
          &nbsp;&nbsp;&nbsp;
          <?php
} ?>
          <input type="submit" value="Add" onclick="document.forms['xfieldsform'].xfieldssubactionadd.value = 'add';">
        </td>
      </tr>
    </table>
  </form>
<?php
      echofooter();
    }
    break;
  case "list":
    $xfieldsdata = xfieldsdataload();
    foreach ($xfields as $name => $value) {
        if (eregi("<\?", $value)) {
            continue;
        }
        $fieldname = safehtml($value[0]);
        if (!$xfieldsadd) {
            $fieldvalue = $xfieldsdata[$xfieldsid][$value[0]];
            $fieldvalue = replace_news("admin", $fieldvalue);
            $fieldvalue = safehtml($fieldvalue);
        } elseif ($value[3] != "select") {
            $fieldvalue = safehtml($value[4]);
        }

        $holderid = "xfield_holder_$fieldname";

        if ($value[3] == "textarea") {
            $smilies_output = xfSmilies("xf_$fieldname", 4);
            $output = <<<HTML
<tr id="$holderid">
  <td width="75" valign="top">
    <br />
    $value[1]
    [if-optional]
    <br />
    <span class="smallesttext">(optional)</span>
    [/if-optional]
  </td>
  <td[if-edit] width="464" colspan="3"[/if-edit]>
    <textarea rows="10" cols="74" name="xfield[$fieldname]" id="xf_$fieldname">$fieldvalue</textarea>
  </td>
  <td width="[if-add]108[/if-add][if-edit]103[/if-edit]" valign="top" align="center">
    <br />
    <a href="#" onclick="window.open('index.php?mod=images&action=quick&area=xf_$fieldname', '_Addimage', 'width=360,height=500,resizable=yes,scrollbars=yes');return false;" target="_Addimage">[insert image]</a>
    <br />
    <a href="#" onclick="window.open('index.php?mod=about&action=cutecode&target=xf_$fieldname', '_Addimage', 'width=360,height=280,resizable=yes,scrollbars=yes');return false;" target="_CuteCode">[quick tags]</a>
    <br />
    <br />
    $smilies_output
  </td>
</tr>
HTML;
        } elseif ($value[3] == "text") {
            $output = <<<HTML
<tr id="$holderid">
  <td width="75" valign="middle">
    $value[1]
  </td>
  <td valign="middle" [if-edit]width="464" colspan="4"[/if-edit][if-add]width="575" colspan="4"[/if-add]>
    <input type="text" name="xfield[$fieldname]" size="[if-optional]42[/if-optional][not-optional]55[/not-optional]"[if-edit] value="$fieldvalue"[/if-edit][if-add] value="$fieldvalue"[/if-add]>[if-optional]&nbsp;&nbsp;&nbsp;<span class="smallesttext">(optional)</span>[/if-optional]
  </td>
</tr>
HTML;
        } elseif ($value[3] == "select") {
            $output = <<<HTML
<tr id="$holderid">
  <td width="75" valign="middle">
    $value[1]
  </td>
  <td valign="middle" [if-edit]width="464" colspan="4"[/if-edit][if-add]width="575" colspan="4"[/if-add]>
    <select name="xfield[$fieldname]">

HTML;
            foreach (explode("\r\n", $value[4]) as $index => $value) {
                if (eregi("<\?", $value)) {
                    continue;
                }
                $value = safehtml($value);
                $output .= "      <option value=\"$index\"" . ($fieldvalue == $value ? " selected" : "") . ">$value</option>\r\n";
            }
            $output .= <<<HTML
    </select>
  </td>
</tr>
HTML;
        }
        $output = preg_replace("'\\[if-optional\\](.*?)\\[/if-optional\\]'s", $value[5] ? "\\1" : "", $output);
        $output = preg_replace("'\\[not-optional\\](.*?)\\[/not-optional\\]'s", $value[5] ? "" : "\\1", $output);
        $output = preg_replace("'\\[if-add\\](.*?)\\[/if-add\\]'s", ($xfieldsadd) ? "\\1" : "", $output);
        $output = preg_replace("'\\[if-edit\\](.*?)\\[/if-edit\\]'s", (!$xfieldsadd) ? "\\1" : "", $output);
        echo $output;
    }
    echo <<<HTML
<script type="text/javascript">
<!--
  var item = null;
  if (document.getElementById) {
    item = document.getElementById("category");
  } else if (document.all) {
    item = document.all["category"];
  } else if (document.layers) {
    item = document.layers["category"];
  }
  if (item) {
    onCategoryChange(item.value);
  }
// -->
</script>
HTML;
    break;
  case "init":
    $postedxfields = $_POST["xfield"];
    $newpostedxfields = array();
    foreach ($xfields as $name => $value) {
        if (eregi("<\?", $value)) {
            continue;
        }
        if ($value[2] != "" and
          !in_array($category, explode(",", $value[2]))) {
            continue;
        }
        if ($value[5] == 0 and
          $postedxfields[$value[0]] == "") {
            msg("error", "XFields Error", "You should fill in all required fields.<br /><a href=\"javascript:history.go(-1)\">go back</a>");
        }
        if ($value[3] == "select") {
            $options = explode("\r\n", $value[4]);
            $postedxfields[$value[0]] = $options[$postedxfields[$value[0]]];
        }
        $newpostedxfields[$value[0]] = replace_news("add", $postedxfields[$value[0]], $n_to_br, $use_html);
    }
    $postedxfields = $newpostedxfields;
    break;
  case "save": // Make sure it is first initialized
    if (!empty($postedxfields)) {
        $xfieldsdata = xfieldsdataload();
        $xfieldsdata[$xfieldsid] = $postedxfields;
        @xfieldsdatasave($xfieldsdata);
    }
    break;
  case "delete":
    $xfieldsdata = xfieldsdataload();
    unset($xfieldsdata[$xfieldsid]);
    @xfieldsdatasave($xfieldsdata);
    break;
  case "templatereplace":
    $xfieldsdata = xfieldsdataload();
    $xfieldsoutput = $xfieldsinput;

    foreach ($xfields as $index10 => $value) {
        if (eregi("<\?", $value)) {
            continue;
        }
        $preg_safe_name = preg_quote($value[0], "'");

        if ($value[5] != 0) {
            if (empty($xfieldsdata[$xfieldsid][$value[0]])) {
                $xfieldsoutput = preg_replace("'\\[xfgiven_{$preg_safe_name}\\].*?\\[/xfgiven_{$preg_safe_name}\\]'is", "", $xfieldsoutput);
            } else {
                $xfieldsoutput = preg_replace("'\\[xfgiven_{$preg_safe_name}\\](.*?)\\[/xfgiven_{$preg_safe_name}\\]'is", "\\1", $xfieldsoutput);
            }
        }
        $xfieldsoutput = preg_replace("'\\[xfvalue_{$preg_safe_name}\\]'i", $xfieldsdata[$xfieldsid][$value[0]], $xfieldsoutput);
    }
    break;
  case "templatereplacepreview":
    $xfieldsoutput = $xfieldsinput;

    foreach ($xfields as $index11 => $value) {
        if (eregi("<\?", $value)) {
            continue;
        }
        $preg_safe_name = preg_quote($value[0], "'");

        if ($value[3] == "select") {
            $options = explode("\r\n", $value[4]);
            $xfield[$value[0]] = $options[$xfield[$value[0]]];
        }
        $xfield[$value[0]] = replace_news("add", $xfield[$value[0]], $n_to_br, $use_html);

        if ($value[5] != 0) {
            if (empty($xfield[$value[0]])) {
                $xfieldsoutput = preg_replace("'\\[xfgiven_{$preg_safe_name}\\].*?\\[/xfgiven_{$preg_safe_name}\\]'is", "", $xfieldsoutput);
            } else {
                $xfieldsoutput = preg_replace("'\\[xfgiven_{$preg_safe_name}\\](.*?)\\[/xfgiven_{$preg_safe_name}\\]'is", "\\1", $xfieldsoutput);
            }
        }
        $xfieldsoutput = preg_replace("'\\[xfvalue_{$preg_safe_name}\\]'i", $xfield[$value[0]], $xfieldsoutput);
    }
    break;
  case "categoryfilter":
    echo <<<HTML
  <script language="javascript">
  function ShowOrHideEx(id, show) {
    var item = null;

    if (document.getElementById) {
      item = document.getElementById(id);
    } else if (document.all) {
      item = document.all[id];
    } else if (document.layers){
      item = document.layers[id];
    }
    if (item && item.style) {
      item.style.display = show ? "" : "none";
    }
  }
  function xfInsertText(text, element_id) {
    var item = null;
    if (document.getElementById) {
      item = document.getElementById(element_id);
    } else if (document.all) {
      item = document.all[element_id];
    } else if (document.layers){
      item = document.layers[element_id];
    }
    if (item) {
      item.focus();
      item.value = item.value + " " + text;
      item.focus();
    }
  }
  function onCategoryChange(value) {

HTML;
    foreach ($xfields as $xfieldsindex1 => $value) {
        if (eregi("<\?", $value)) {
            continue;
        }
        $categories = str_replace(",", "||value==", $value[2]);
        if ($categories) {
            echo "    ShowOrHideEx(\"xfield_holder_{$value[0]}\", value == $categories);\r\n";
        }
    }
    echo "  }\r\n</script>";
    break;
// Sort by XField v1.0 - addblock
  case 'noop':
    break;
// Sort by XField v1.0 - End addblock
  default:
    msg("error", "XFields Error", "Invalid Request, maybe you found a bug in XFields.");
}
?>
