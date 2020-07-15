<?php
if ($member_db[1] > 3 or ($member_db[1] != 1 and $action == "doimagedelete" and $config_user_image_delete == "no")) {
    msg("error", "Access Denied", "You don't have permission to manage images");
}

// for the imageResize hack only jpg, jpeg, gif & png is allowed to upload.  And output is always .jpg
$allowed_extensions = array("jpg", "jpeg","gif","png");


// ********************************************************************************
// Show Images List
// ********************************************************************************
if ($action != "doimagedelete") {
    if ($action == "quick") {
        echo"<html>
			<head>
			<title>Insert Image</title>
$skin_css
                        </head>
                        <body>
			<script language=\"javascript\" type=\"text/javascript\">
			<!--
			function insertimage(text) {
			        text = ' ' + text + ' ';
			        opener.document.forms['addnews'].$area.focus();
			        opener.document.forms['addnews'].$area.value  += text;
			        opener.document.forms['addnews'].$area.focus();
			 	    window.close();
			}
			//-->
			</script>
			</head>
			<body>
			<div id=\"popup\">
			<h1>Upload Image</h1>
";
    } else {
        echoheader("images", "Manage Images");
    }

    if ($subaction == "upload") {
        if (!$image) {
            $image = $_FILES['image']['tmp_name'];
        }
        if (!$image_name) {
            $image_name = $_FILES['image']['name'];
        }
        $image_name = str_replace(" ", "_", $image_name);

        $img_name_arr = explode(".", $image_name);
        $type          = end($img_name_arr);

        if ($image_name == "") {
            $img_result = "<br /><font class=error>No File Specified For Upload !!!</font>";
        } elseif (!isset($overwrite) and file_exists($config_path_image_upload."/".$image_name)) {
            $img_result = "<br /><font class=error>Image already exist !!!</font>";
        } elseif (!(in_array($type, $allowed_extensions) or in_array(strtolower($type), $allowed_extensions))) {
            $img_result = "<br /><font class=error>This type of file is not allowed !!!</font>";
        } else {
            @copy($image, $config_path_image_upload."/".$image_name) or $img_result = "<font class=error>Couldn't copy image to server</font><br />Check if file_uploads is allowed in the php.ini file of your server";
            if (file_exists($config_path_image_upload."/".$image_name)) {
                $img_result = "ok"; // if file is uploaded succesfully
            }
            // The resizeimage hack by torben rasmussen
            // checks if original image has been uploadet and then grabs it
            if ($img_result == "ok") {
                $img_pieces = explode(".", $image_name);
                if (in_array($img_pieces[count($img_pieces)-1], $allowed_extensions)) {
                    $new_image = time().".".$img_pieces[count($img_pieces)-1];
                    copy($config_path_image_upload."/$image_name", $config_path_image_upload."/$new_image");
                    echo("<b><font class=pass>Image was uploaded</font></b><br />");
                    echo("<img border=\"0\" alt=\"\" src='$config_path_image_upload/$new_image' /><br />");
                    echo("<br />Saved as: $config_path_image_upload/$new_image");
                }
            }
            if ($img_result = "ok") {
                unlink($config_path_image_upload."/".$image_name);
            }
        }
    }

    echo"<table border=0 cellpadding=0 cellspacing=0  width=100%>
    <FORM action='$PHP_SELF?mod=images' METHOD='POST' ENCTYPE=\"multipart/form-data\">
   	<input type=hidden name=subaction value=upload>
	<input type=hidden name=area value='$area'>
    <input type=hidden name=action value='$action'>
    <td height=33>

<table border=0 cellpadding=0 cellspacing=0 class=\"panel\" cellpadding=8>
    <tr>
    <td height=25>
    <input type=file name=image size=23>&nbsp;&nbsp; <input type=submit value='Upload'><br />
    <input type=checkbox class=checkbox name=overwrite value=1> Overwrite if exist?
    <b>$img_result</b></tr>
    </form>
    </table>
    <tr>
	<td height=11>
        <img height=20 border=0 src=\"skins/images/blank.gif\" width=1 />
    </tr><tr>
	<td  height=14>
    <b>Uploaded Images</b>
    </tr>
    <tr>
	<td height=1>
<FORM action='$PHP_SELF?mod=images' METHOD='POST'>
  <table width=100% heigth=100% cellspacing=2 cellpadding=0>";

    $img_dir = opendir($config_path_image_upload);

    $i = 0;
    while ($file = readdir($img_dir)) {
        $img_name_arr = explode(".", $file);
        $img_type      = end($img_name_arr);

        if ((in_array($img_type, $allowed_extensions) or in_array(strtolower($img_type), $allowed_extensions)) and $file != ".." and $file != "." and is_file($config_path_image_upload."/".$file)) {
            $i++;
            $this_size =  filesize($config_path_image_upload."/".$file);
            $total_size += $this_size;
            $img_info = getimagesize($config_path_image_upload."/".$file);
            if ($i%2 != 0) {
                $bg = "class=altern1";
            } else {
                $bg = "class=altern2";
            }
            $image_file = "http://".str_replace("//", "/", str_replace("/./", "/", str_replace("http://", "", "$config_http_script_dir/$config_path_image_upload/$file")));
            if ($action == "quick") {
                $my_area = str_replace("_", " ", $area);
                echo"
                <tr $bg>
			    <td height=16 width=57%>
                <a title=\"Insert this image in the $my_area\" href=\"javascript:insertimage('&lt;img style=&quot;border: none;&quot; alt=&quot;&quot; src=&quot;$image_file&quot; /&gt;')\"><img style=float:left src=\"$image_file\" width=\"60\" />$file</a>

                <td height=16 align=right>
			    $img_info[0]x$img_info[1]

			    <td height=16 align=right>
	    	    &nbsp;". formatsize($this_size) ."
			    </tr>";
            } else {
                echo"<tr $bg>
			    <td height=16 width=63% >
			    <a target=_blank href=\"$image_file\"><img style=\"float:left;margin-right:5px\" border=\"0\" alt=\"\" src=\"$image_file\" width=\"60\" /><b>$file</b></a>

                <td height=16 align=right>
			    $img_info[0]x$img_info[1]

			    <td height=16 align=right>
	    	    &nbsp;". formatsize($this_size) ."
	    	    <td width=70 height=16 align=right>
                <input type=checkbox class=checkbox name=images[$file] value=\"$file\">
			    </tr>";
            }
        }
    }

    if ($i > 0) {
        echo"<tr ><td height=16>";

        if ($action != "quick") {
            echo" <td colspan=4 align=right>
                   <br /><input type=submit value='Delete Selected Images'>
			    </tr>";
        }
        echo"<tr heigh=1>
		<td >
		<br /><b>Total size</b>
	    <td>&nbsp;
	    <td align=right>
		<br /><b>". formatsize($total_size) .'</b>
		</tr>';
    }
    echo'
   </table><input type=hidden name=action value=doimagedelete></form></table>';
    if ($action != "quick") {
        echofooter();
    }
}
// ********************************************************************************
// Delete Image
// ********************************************************************************
elseif ($action == "doimagedelete") {
    if (!isset($images)) {
        msg("info", "No Images selected", "You must select images to be deleted.", "$PHP_SELF?mod=images");
    }
    //	if(!file_exists($config_path_image_upload."/".$image) or !$image){ msg("error","Error","Could not delete image", "$PHP_SELF?mod=images"); }
    foreach ($images as $null => $image) {
        unlink($config_path_image_upload."/".$image) or print("Could not delete image <strong>$file</strong>");
    }
    msg("info", "Image(s) Deleted", "The image(s) were successfully deleted.", "$PHP_SELF?mod=images");
}
