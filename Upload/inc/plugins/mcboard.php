<?php

if(!defined('IN_MYBB'))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook("admin_config_menu", "mcboard_admin_config_menu");
$plugins->add_hook("admin_config_action_handler", "mcboard_admin_config_action_handler");
$plugins->add_hook("index_start", "mcboard_start");

function mcboard_info() // Informacje
{
	return array(
		"name"			=> "MCBoard",
		"description"	=> "Wtyczka MCBoard pozwala dodawać serwery minecraft na forum. Plugin utowrzony na podstawie CsBoard oraz klasy php autorstwa xPaw.",
		"website"		=> "http://www.mybboard.pl",
		"author"		=> "Matslom",
		"authorsite"	=> "matslom.pl",
		"version"		=> "1.0.0",
		'guid'          => '*',
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
	global $db;
	
$db->write_query("CREATE TABLE ".TABLE_PREFIX."mcboard (`id` int(10) unsigned NOT NULL AUTO_INCREMENT,`ip` varchar(50) NOT NULL DEFAULT '0',`port` varchar(10) NOT NULL DEFAULT '0', `rodzaj` int(1) NOT NULL DEFAULT '0',PRIMARY KEY (`id`)) ENGINE = MyISAM;");

	$template_table = '<table border="0" cellspacing="1" cellpadding="3" class="tborder">
		<tr>
		<td class="thead" align="center" colspan="7">
		<strong>Serwery</strong>
		</td>
		</tr>
		<td class="tcat" colspan="0"><span class="smalltext"><strong>Nazwa:</strong></span></td>
		<td class="tcat" colspan="0"><span class="smalltext"><strong>IP:</strong></span></td>
		<td class="tcat" colspan="0"><span class="smalltext"><strong>Sloty:</strong></span></td>
		<td class="tcat" colspan="0"><span class="smalltext"><strong>Mapa:</strong></span></td>
		<td class="tcat" colspan="0"><span class="smalltext"><strong>Rodzaj:</strong></span></td>
		<td class="tcat" colspan="0"><span class="smalltext"><strong>Status:</strong></span></td>
		<td class="tcat" colspan="0"><span class="smalltext"><strong>Wersja:</strong></span></td>
		{$mcboard_row}
		<tr><td class="trow1" align="center" colspan="7"> Na naszych {$serwery_l} {$lang_serwery}, które mają w sumie {$slot_l} {$lang_sloty} jest {$gracze_l} {$lang_gracze} online.  </td></tr>
        </table><br />';
    $template_row = '<tr>
        <td class="trow2"> <span class="smalltext">{$dane[\'HostName\']}</span></td>
        <td class="trow2"> <span class="smalltext">{$mc[\'ip\']}:{$mc[\'port\']}</span></td>
        <td class="trow2"> <span class="smalltext">{$dane[\'Players\']}/{$dane[\'MaxPlayers\']}</span></td>
		<td class="trow2"> <span class="smalltext">{$dane[\'Map\']}</span></td>
        <td class="trow2"> <span class="smalltext">{$mc[\'rodzaj\']}</span></td>
        <td class="trow2"> <span class="smalltext">{$status}</span></td>
        <td class="trow2"> <span class="smalltext"><a title="{$dane[\'Software\']}">{$dane[\'Version\']}</a><span></td>
        </tr>';

    $db->write_query("INSERT INTO `".TABLE_PREFIX."templates` VALUES (NULL, 'mcboard', '".$db->escape_string($template_table)."', '-1', '1', '', '".time()."')");
	$db->write_query("INSERT INTO `".TABLE_PREFIX."templates` VALUES (NULL, 'mcboard_row', '".$db->escape_string($template_row)."', '-1', '1', '', '".time()."')");
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
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title IN('mcboard', 'mcboard_row')");
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
    global $mcboard, $db, $theme, $templates;;
	
$query = $db->simple_select('mcboard', '*');
require_once MYBB_ROOT."inc/plugins/minequery/MinecraftQuery.class.php";
$Query = new MinecraftQuery();
Ini_Set( 'display_errors', false );


while($mc = $db->fetch_array($query)) {
    if(!$mc['rodzaj'] == 2) {
        $mc['rodzaj'] = 'Premium';
    }
    elseif($mc['rodzaj'] == 1) {
        $mc['rodzaj'] = 'NonPremium';
    }
        
    $IP = $mc['ip'];
    $PORT = $mc['port']; 
			
	try {
 		$Query->Connect($IP, $PORT, 1);
    }
    catch( MinecraftQueryException $e ) {
        $error = $e->getMessage();
    }
     $dane = $Query->GetInfo();

	
	if($info['serverlocked'] !== 0) {
		$info['serverlocked'] = 'Offline';
	}
	elseif($info['serverlocked'] == 0) {
		$info['serverlocked'] = 'Online';
	}
	
	if ($dane['Players'] === NULL) {
		$dane['Players'] = '-';
	}
	if($dane['MaxPlayers'] == NULL){
		$status = 'Offline';
	} 
	else {
		$status = 'Online';
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

	if ($serwery_l == '1'){
    	$lang_serwery = 'serwerze'; 
    }
    else {               
		$lang_serwery = 'serwerach';
   	}

   	if ($gracze_l == '1') {
   		$lang_gracze = 'gracz';
   	}
   	else {
   		$lang_gracze = 'graczy';
   	}

   	if ($slot_l < '5') {
   		$lang_sloty = 'sloty';
   	}
   	else {
   		$lang_sloty ='slotów';
   	}

    eval('$mcboard = "'.$templates->get('mcboard').'";');

}
?>
