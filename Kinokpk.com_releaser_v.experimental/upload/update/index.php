<?php
/**
 * Updater from 3.00 to 3.30
 * @license GNU GPLv3 http://opensource.org/licenses/gpl-3.0.html
 * @package Kinokpk.com releaser
 * @author ZonD80 <admin@kinokpk.com>
 * @copyright (C) 2008-now, ZonD80, Germany, TorrentsBook.com
 * @link http://dev.kinokpk.com
 */
define('ROOT_PATH',str_replace('update','',dirname(__FILE__)));


if ($_GET['setlang']) {
	setcookie('lang',(string)$_GET['setlang']);
	print('<html><head><meta http-equiv="Content-Type" content="text/html; charset=windows-1251" /></head><a href="index.php">���������� / Continue</a></html>');
	die();
}
if (!$_COOKIE['lang'] || (strlen($_COOKIE['lang'])>2)) {
	print("<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1251\" /></head><h1>�������� ���� / Choose a language: <a href=\"index.php?setlang=ru\">�������</a>, <a href=\"index.php?setlang=en\">English</a></h1></html>");
	die();
}

require_once(ROOT_PATH.'include/bittorrent.php');

	/* @var database object */
	require_once(ROOT_PATH . 'classes/database/database.class.php');
	$REL_DB = new REL_DB($mysql_host, $mysql_user, $mysql_pass, $mysql_db, $mysql_charset);
	
$step = (int)$_GET['step'];

	/* @var object links parser/adder/changer for seo */
	require_once(ROOT_PATH . 'classes/seo/seo.class.php');
	$REL_SEO = new REL_SEO();
	
	$REL_CONFIG['lang'] = substr(trim((string)$_COOKIE['lang']),0,2);
	$REL_CONFIG['static_language'] = 'ru=update/lang/ru.lang,en=update/lang/en.lang';
	/* @var object language system */
	require_once(ROOT_PATH . 'classes/lang/lang.class.php');
$REL_LANG = new REL_LANG($REL_CONFIG);
//var_dump($REL_LANG->lang);
function headers2() {
	global $step, $REL_LANG;
	header("X-Powered-By: Kinokpk.com releaser ".RELVERSION);
	header("Cache-Control: no-cache, must-revalidate, max-age=0");
	//header("Expires:" . gmdate("D, d M Y H:i:s") . " GMT");
	header("Expires: 0");
	header("Pragma: no-cache");
	print('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
<title>'.$REL_LANG->_("Kinokpk.com releaser 3.00 to 3.30 updater").', '.$REL_LANG->_("step").': '.$step.'</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" /></head><body>');

	if (ini_get("register_globals")) die('<font color="red" size="20">'.$REL_LANG->_("Turn off register globals, noob!").'</font>');

}

function footers() {
global $REL_LANG;
	print('<hr /><div align="right">'.$REL_LANG->_("Kinokpk.com releaser 3.00 to 3.30 updater").'</div></body></html>');
}

function cont($step) {
	global $REL_LANG;
	print '<a href="index.php?step='.$step.'">'.$REL_LANG->_("Continue").'</a>';

}

function hr() {
print '<hr/>';
}

headers2();

if (!$step) {
	print $REL_LANG->_("You must agree with the following GNU GPLv3 licence to continue");
	hr();
	print('<iframe width="100%" height="300px" src="gnu.html">GNU</iframe>');
	hr();
	print $REL_LANG->_("Please do not forget to make some backups before update");
	hr();
	print $REL_LANG->_("This update will clear all themes settings and set theme with id=1 to all users due highest code changes");
	hr();
	print $REL_LANG->_("Next step will change database schema");
	hr();
	cont(1);
}

elseif ($step==1) {
	$strings = file(ROOT_PATH."update/update.sql");
	$query = '';
	foreach ($strings AS $string)
	{
		if (preg_match("/^\s?#/", $string) || !preg_match("/[^\s]/", $string))
		continue;
		else
		{
			$query .= $string;
			if (preg_match("/;/", $query))
			{
				$REL_DB->query($query) or die($REL_LANG->_("SQL error happened").' ['.mysql_errno().']: ' . mysql_error(). ',<hr/>'.$REL_LANG->_("Query").': '.$query.'<hr/>'.$REL_LANG->_('Recover with backup and <a href="javascript:history.go(-1);">try again</a> please'));
				$query = '';
			}
		}
	}

	print $REL_LANG->_('<font color="green">This step of installation was successed</font>');
	hr();
	print $REL_LANG->_("Next step will change database schema");
	hr();
	print $REL_LANG->_("Next step will convert comments to new storing structure");
	hr();
	cont(2);
}

elseif ($step==2) {
  $export = array('pollcomments'=>'poll', 'newscomments'=>'news', 'usercomments'=>'userid', 'reqcomments'=>'request', 'rgcomments'=>'relgroup','rgnewscomments'=>'rgnews');
  foreach (array_keys($export) as $exp) {
    $REL_DB->query("INSERT INTO comments (user, toid, added, text, ip, type) SELECT user, {$export[$exp]}, added, text, ip, '".str_replace('comments','',$exp)."' FROM $exp") or die($REL_LANG->_("SQL error happened").' ['.mysql_errno().']: ' . mysql_error(). ',<hr/>'.$REL_LANG->_("Query").': '.$query.'<hr/>'.$REL_LANG->_('Recover with backup and <a href="javascript:history.go(-1);">try again</a> please'));
    print $REL_LANG->_("%s moved",$exp);
    hr();
    $REL_DB->query("DROP table $exp") or die($REL_LANG->_("SQL error happened").' ['.mysql_errno().']: ' . mysql_error(). ',<hr/>'.$REL_LANG->_("Query").': '.$query.'<hr/>'.$REL_LANG->_('Recover with backup and <a href="javascript:history.go(-1);">try again</a> please'));
    print $REL_LANG->_("%s table dropped",$exp);
    hr();
    }
    	print $REL_LANG->_('<font color="green">This step of installation was successed</font>');
	hr();
	print $REL_LANG->_("Next step will change database schema");
	hr();
	print $REL_LANG->_("Next step will convert notification methods");
	hr();
	cont(3);
}

elseif($step==3) {
	$res = $REL_DB->query("SELECT notifs,emailnotifs,id FROM users order by id asc");
while ($row = mysql_fetch_assoc($res)) {
$notifs = explode(',',$row['notifs']);
$emailnotifs = explode(',',$row['emailnotifs']);
foreach ($notifs as $key=>$notify) {
if ($notify=='comments') $notifs[$key]='relcomments';
}
foreach ($emailnotifs as $key=>$notify) {
if ($notify=='comments') $emailnotifs[$key]='relcomments';
}
$REL_DB->query("update users set notifs='".implode(',',$notifs)."',emailnotifs='".implode(',',$emailnotifs)."' where id={$row['id']}") or die($REL_LANG->_("SQL error happened").' ['.mysql_errno().']: ' . mysql_error(). ',<hr/>'.$REL_LANG->_("Query").': '.$query.'<hr/>'.$REL_LANG->_('Recover with backup and <a href="javascript:history.go(-1);">try again</a> please'));
print $REL_LANG->_('User with id %s updated',$row['id']);
hr();
}
    	print $REL_LANG->_('<font color="green">This step of installation was successed</font>');
	hr();
	print $REL_LANG->_("Next step will change database schema");
	hr();
	print $REL_LANG->_("Next step will convert block display settings");
	hr();
	cont(4);
	}
elseif($step==4) {
$res = $REL_DB->query("SELECT bid,which FROM orbital_blocks WHERE which LIKE '%ihome%'") or die($REL_LANG->_("SQL error happened").' ['.mysql_errno().']: ' . mysql_error(). ',<hr/>'.$REL_LANG->_("Query").': '.$query.'<hr/>'.$REL_LANG->_('Recover with backup and <a href="javascript:history.go(-1);">try again</a> please'));
while ($row = mysql_fetch_assoc($res)) {
$REL_DB->query("UPDATE orbital_blocks SET which=".sqlesc(str_replace('ihome','index',$row['which']))." WHERE bid={$row['bid']}") or die($REL_LANG->_("SQL error happened").' ['.mysql_errno().']: ' . mysql_error(). ',<hr/>'.$REL_LANG->_("Query").': '.$query.'<hr/>'.$REL_LANG->_('Recover with backup and <a href="javascript:history.go(-1);">try again</a> please'));
print $REL_LANG->_("Block with id %s done",$row['bid']);
hr();
}
    	print $REL_LANG->_('<font color="green">This step of installation was successed</font>');
	hr();
	print $REL_LANG->_("Next step will install languages");
	hr();
	cont(5);
}

elseif ($step==5) {
$REL_LANG->import_langfile(ROOT_PATH.'install/lang/import/en.lang','en');
$REL_LANG->import_langfile(ROOT_PATH.'install/lang/import/ru.lang','ru');
    	print $REL_LANG->_('<font color="green">This step of installation was successed</font>');
	hr();
	print $REL_LANG->_("Next step will clear caches and finalize update");
	hr();
	print $REL_LANG->_("Next step will convert block display settings");
	hr();
	cont(6);
}

elseif ($step==6) {
  $REL_CACHE->clearAllCache();
  print $REL_LANG->_('<h1>Update to 3.30 complete. Please delete "install" and "update" folders from your server.</h1>');
  hr();
  print $REL_LANG->_("Donate to project:");
  ?>
  <p><pre>�� ������ ������ ������ ����������� ��������� ������ (�� ������ �������), ���������:
Webmoney: U361584411086 E326225084100 R153898361884 Z113282224168,
Yandex.������: 41001423787643,
Paypal: zond80@gmail.com</pre></p><hr /><div align="right"><i>� ���������, ������������ Kinokpk.com releaser</i></div>
<?php
}
footers();
?>
