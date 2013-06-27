<?php

/* 
 * Plugin MCBoard for MyBB by Matslom [matslom.pl]; Copyright (C) 2012-2013
 * Github https://github.com/Matslom/Mc-board.git
 * License GNU GENERAL PUBLIC LICENSE -> http://www.gnu.org/licenses/gpl-3.0.txt
 * Do not remove this information
 */


if(!defined('IN_MYBB')){
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook("admin_config_menu", "mcboard_admin_config_menu");
$plugins->add_hook("admin_config_action_handler", "mcboard_admin_config_action_handler");
$plugins->add_hook("index_start", "mcboard_start");

function mcboard_info() // Informacje
{
	global $lang;
	$lang->load("mcboard");

	return array(
		"name"			=> $lang->plug_title,
		"description"	=> $lang->plug_desc,
	"website"		=> "https://github.com/Matslom/Mc-board",
		"author"		=> "Matslom",
		"authorsite"	=> "http://matslom.pl/",
		"version"		=> "1.0.0",
		'guid'          => '5260438caebf4fcfbc3c8faefc67848a',
		'compatibility' => '16*'
	);
}

function mcboard_activate() // Aktywacja
{	
	require MYBB_ROOT."/inc/adminfunctions_templates.php";
	find_replace_templatesets("index", '#'.preg_quote('{$header}').'#', '{$header}{$mcboard}');
}

function mcboard_deactivate() // Dezaktywacja
{
	include MYBB_ROOT."/inc/adminfunctions_templates.php";
	find_replace_templatesets('index', '#' . preg_quote('{$mcboard}') . '#', '', 0);
}

function mcboard_install() //Instalcja
{
	global $db, $lang;
	$lang->load("mcboard");

//nowa tabela	
$db->write_query("CREATE TABLE ".TABLE_PREFIX."mcboard (`id` int(10) unsigned NOT NULL AUTO_INCREMENT,`ip` varchar(50) NOT NULL DEFAULT '0',`port` varchar(10) NOT NULL DEFAULT '0', `rodzaj` int(1) NOT NULL DEFAULT '0',PRIMARY KEY (`id`)) ENGINE = MyISAM;");

//szablony
	$template_table = '<table border="0" cellspacing="1" cellpadding="3" class="tborder">
		<tr>
		<td class="thead" align="center" colspan="8">
		<strong>Serwery</strong> <div class="expcolimage"><img src="{$theme[\'imgdir\']}/collapse.gif" id="mc-board_img" class="expander" alt="{$expaltext}" title="{$expaltext}" /></div>
		</td>
		</tr>
		<tbody style="{$expdisplay}" id="mc-board_e">
		<td class="tcat" colspan="0"><span class="smalltext"><strong>{$lang->name}:</strong></span></td>
		<td class="tcat" colspan="0"><span class="smalltext"><strong>{$lang->ip}:</strong></span></td>
		<td class="tcat" colspan="0"><span class="smalltext"><strong>{$lang->slott}:</strong></span></td>
		<td class="tcat" colspan="0"><span class="smalltext"><strong>{$lang->map}:</strong></span></td>
		<td class="tcat" colspan="0"><span class="smalltext"><strong>{$lang->type}:</strong></span></td>
		<td class="tcat" colspan="0"><span class="smalltext"><strong>{$lang->status}:</strong></span></td>
		<td class="tcat" colspan="0"><span class="smalltext"><strong>{$lang->version}:</strong></span></td></tr>
		{$mcboard_row}
		{$mcboard_summation}
        <tbody></table><br />';
    $template_row = '<tr>
        <td class="trow2"> <span class="smalltext">{$dane[\'HostName\']}</span></td>
        <td class="trow2"> <span class="smalltext">{$mc[\'ip\']}:{$mc[\'port\']}</span></td>
        <td class="trow2"> <span class="smalltext">{$dane[\'Players\']}/{$dane[\'MaxPlayers\']}</span></td>
		<td class="trow2"> <span class="smalltext">{$dane[\'Map\']}</span></td>
        <td class="trow2"> <span class="smalltext">{$mc[\'rodzaj\']}</span></td>
        <td class="trow2"> <span class="smalltext">{$status}</span></td>
        <td class="trow2"> <span class="smalltext"><a title="{$dane[\'Software\']}">{$dane[\'Version\']}</a><span></td>
        </tr>';
    $template_summation = '<tr><td class="trow1" align="center" colspan="8"> {$lang_servers} {$lang_sloty} {$lang_gracze}  </td></tr>';


    $db->write_query("INSERT INTO `".TABLE_PREFIX."templates` VALUES (NULL, 'mcboard', '".$db->escape_string($template_table)."', '-1', '1', '', '".time()."')");
	$db->write_query("INSERT INTO `".TABLE_PREFIX."templates` VALUES (NULL, 'mcboard_row', '".$db->escape_string($template_row)."', '-1', '1', '', '".time()."')");
	$db->write_query("INSERT INTO `".TABLE_PREFIX."templates` VALUES (NULL, 'mcboard_summation', '".$db->escape_string($template_summation)."', '-1', '1', '', '".time()."')");

//ustawienia
$db->write_query("INSERT INTO `".TABLE_PREFIX."settinggroups` VALUES (NULL, 'mcboard', 'MCBoard', '".$lang->conf_name."', 1, 0)");
$query = $db->simple_select("settinggroups", "gid", "name = 'mcboard'", array("limit" => 1));
$gid = $db->fetch_field($query, "gid");

	$mcboard_sum = array(
		'name'			=> 'mcboard_sum',
		'title'			=> $lang->conf_summation_title,
		'description'	=> $lang->conf_summation_desc,
		'optionscode'	=> 'yesno', 
		'value'			=> '0', 
		'disporder'		=> '1', 
		'gid'			=> intval($gid)
	);


	$db->insert_query('settings', $mcboard_sum);
	rebuild_settings();  
}



 function mcboard_is_installed() // Zainstalowany
 {
	 	global $db;

	if($db->table_exists('mcboard'))
	{
		return true;
	}
	
	return false;
 }

function mcboard_uninstall() // Odinstalowywanie
{
	global $db;
	
	$db->query("DROP TABLE ".TABLE_PREFIX."mcboard");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title IN('mcboard', 'mcboard_row', 'mcboard_summation')");
	$db->write_query("DELETE FROM ".TABLE_PREFIX."settings WHERE name IN ('mcboard_sum')");
	$db->write_query("DELETE FROM ".TABLE_PREFIX."settinggroups WHERE name = 'mcboard'");
}
 
function mcboard_admin_config_menu(&$sub_menu)
{
   $sub_menu[] = array("title" => "MCBoard", "link" => "index.php?module=config/mcboard");
} 

function mcboard_admin_config_action_handler(&$actions)
{
   $actions['mcboard'] = array('file' => 'mcboard.php');
}

function mcboard_start() {
    global $mcboard, $db, $theme, $templates, $lang, $mybb;
	$lang->load("mcboard");

	
$query = $db->simple_select('mcboard', '*');
require_once MYBB_ROOT."inc/plugins/minequery/MinecraftQuery.class.php";


while($mc = $db->fetch_array($query)) {
    if(!$mc['rodzaj'] == 2) {
        $mc['rodzaj'] = $lang->premium;
    }
    elseif($mc['rodzaj'] == 1) {
        $mc['rodzaj'] = $lang->nonpremium;
    }
        
    $IP = $mc['ip'];
    $PORT = $mc['port']; 
			
	try {
		$Query = new MinecraftQuery();
 		$Query->Connect($IP, $PORT, 1);
    }
    catch( MinecraftQueryException $e ) {
        $error = $e->getMessage();
    }
     $dane = $Query->GetInfo();
	
	if ($dane['Players'] === NULL) {
		$dane['Players'] = '-';
	}
	if($dane['MaxPlayers'] == NULL){
		$status = $lang->offline;
	} 
	else {
		$status = $lang->online;
	}

	if ($dane['MaxPlayers'] === NULL) {
		$dane['MaxPlayers'] = '-';
	}
	
	if ($dane['HostName'] == NULL) {
		$dane['HostName'] = '-';
	}
	
	if ($dane['Map'] === NULL) {
		$dane['Map'] = '-';
	}

	if ($dane['Version'] === NULL) {
		$dane['Version'] = '-';
	}

    eval('$mcboard_row .= "'.$templates->get("mcboard_row").'";');
         
	$serwery_l = $db->num_rows($query);      
    $slot_l = $slot_l + $dane['MaxPlayers'];
	$gracze_l = $gracze_l + $dane['Players'];

}

if($mybb->settings['mcboard_sum'] == '1') {

	if ($serwery_l == '1'){
    	$lang_servers = $lang->oneserver; 
    }
    else {               
		$lang_servers = $lang->sprintf($lang->servers, $serwery_l);
   	}

   	if ($gracze_l == '1') {
   		$lang_gracze = $lang->player;
   	}
   	else {
   		$lang_gracze = $lang->sprintf($lang->players, $gracze_l);
   	}

   	if($slot_l == '0') {
   		$lang_sloty = $lang->sprintf($lang->slots, $slot_l);
   	}
   	elseif ($slot_l < '5') {
   		$lang_sloty = $lang->sprintf($lang->slot, $slot_l);
   	}
   	else {
   		$lang_sloty = $lang->sprintf($lang->slots, $slot_l);
   	}
   	eval('$mcboard_summation = "'.$templates->get('mcboard_summation').'";');
}

    eval('$mcboard = "'.$templates->get('mcboard').'";');

}
?>
