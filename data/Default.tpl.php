<?php
///////////////////// TEMPLATE Default /////////////////////
$template_active = <<<HTML
<table border="0" width="420" cellspacing="1" cellpadding="3">
<tr>
<td width="100%" style="text-align:justify">
<b>{title}</b>
</td>
</tr>
<tr>
<td width="100%" style="text-align:justify">
<font style="font-family:georgia, verdana, arial, sans-serif;	color:#666; font-size:14;">{short-story}</font></td>
</tr>
<tr>
<td width="100%">
<table border="0" style="border-top: 1px dotted #f2f3f3" width="408" cellspacing="0">
<tr>
<td width="220"><i><font style="font-family:georgia, verdana, arial, sans-serif; font-size:11;	color:black;">{date} by {author}</font></i><br> </td><td width="168" ><div align=right>
<font style="font-family:georgia, verdana, arial, sans-serif; font-size:11;">[full-link]Read More ...[/full-link]    [com-link]comments ({comments-num})[/com-link]</font><br>
</div>
</td>
</tr>
</table>
</td>
</tr>
</table>
<br />
HTML;


$template_full = <<<HTML
<table border="0" width="420" cellspacing="1" cellpadding="3">
<tr>
<td width="100%" style="text-align:justify">
<b>{title}</b>
</td>
</tr>
<tr>
<td width="100%" style="text-align:justify">
<font style="font-family:georgia, verdana, arial, sans-serif;	color:#666; font-size:14;">{full-story}</font></td>
</tr>
<tr>
<td width="100%">
<table border="0" style="border-top: 1px dotted #f2f3f3" width="408" cellspacing="0">
<tr>
<td width="220"><i><font style="font-family:georgia, verdana, arial, sans-serif;	font-size:11;	color:black;">{date} by {author}</font></i><br> </td><td width="168"><div align=right>
<font style="font-family:georgia, verdana, arial, sans-serif;	font-size:11;">{comments-num} comments</font><br>
</div>
</td>
</tr>
</table>
</td>
</tr>
</table>
<br />
HTML;


$template_comment = <<<HTML
 <table border="0" width="400" height="40" cellspacing="" cellpadding="3">
    <tr>
      <td height="1" style="border-bottom-style: solid;border-bottom-width: 1px; border-bottom-color: black;">by <b>{author}</b> @ {date}</td>
    </tr>
    <tr>
      <td height="40" valign="top" bgcolor="#F9F9F9" >{comment}</td>
    </tr>
  </table>
<br>
HTML;


$template_form = <<<HTML
[logged-in]Feel free to leave a comment.
<table border="0" width="342" cellspacing="0" cellpadding="0">
<tr>
	<td width="51" height="1">
		<p align="left">smile:</p>
		<center>
	<td width="291" height="1" >
		{smilies}</tr>
<tr>
	<td width="340" height="1" colspan="2">
		<textarea cols="40" rows="6" name="comments" tabindex="3"></textarea><br />
		<input type="submit" name="submit" value="   Add My Comment   " accesskey="s"></tr></table>
[/logged-in]

[not-logged-in]
<table border="0" width="342" cellspacing="0" cellpadding="0">
<tr>
	<td width="49" height="1">
		name:
	<td width="289" height="1">
		<input type=text name=name tabindex="1"></tr>
</center>
<tr>
	<td width="51" height="1">
		<p align="left">smile:</p>
		<center>
	<td width="291" height="1" >
		{smilies}</tr>
<tr>
	<td width="340" height="1" colspan="2">
		<textarea cols="40" rows="6" name="comments" tabindex="3"></textarea><br />
		<input type="submit" name="submit" value="   Add My Comment   " accesskey="s"></tr></table>
[/not-logged-in]
HTML;


$template_prev_next = <<<HTML
<p align="center">[prev-link]&laquo; Previous[/prev-link] {pages} [next-link]Next &raquo;[/next-link]</p>
HTML;
$template_comments_prev_next = <<<HTML
<p align="center">[prev-link]&laquo; Older[/prev-link] ({pages}) [next-link]Newer &raquo;[/next-link]</p>
HTML;
