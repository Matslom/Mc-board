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
		"description"	=> "Wtyczka MCBoard pozwala dodawać serwery minecraft na forum. Jest to zmodyfikowana wersja CSBoard autorstwa Cybul.",
		"website"		=> "http://www.mybboard.pl",
		"author"		=> "Matslom",
		"authorsite"	=> "webax.pl",
		"version"		=> "1.0.0",
		'guid'        => '*',
		'compatibility'    => '16*'
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
	
		$db->write_query("
		CREATE TABLE ".TABLE_PREFIX."mcboard (
		    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(50) NOT NULL DEFAULT '0',
  `port` varchar(10) NOT NULL DEFAULT '0',
  `rodzaj` int(1) NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`)
		) ENGINE = MyISAM;
	");
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
    global $mcboard, $db;
	
    $query = $db->simple_select('mcboard', '*');
    $mcboard = '<table border="0" cellspacing="1" cellpadding="3" class="tborder">
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
<td class="tcat" colspan="0"><span class="smalltext"><strong>Wersja:</strong></span></td>';
    while($mc = $db->fetch_array($query)) {
        if(!$mc['rodzaj'] == 2) {
            $mc['rodzaj'] = 'Premium';
        } elseif($mc['rodzaj'] == 1) {
            $mc['rodzaj'] = 'NonPremium';
       }
        $IP = $mc['ip'];
        $PORT = $mc['port']; 

require_once MYBB_ROOT."inc/plugins/minequery/MinecraftQuery.class.php";
		
		try
		{
		$Query = new MinecraftQuery();
 		$Query->Connect($IP, $PORT, 1);

        $dane = $Query->GetInfo();
       }
               catch( MinecraftQueryException $e )
    {
        $error = $e->getMessage( );
    }
        
        /*print_r( $Query->GetPlayers( ) );*/
	
	if($info['serverlocked'] !== 0) {
			
			$info['serverlocked'] = 'Offline';
	} elseif($info['serverlocked'] == 0) {
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
        
        $mcboard .= '
            <tr>
                <td class="trow2"> <span class="smalltext">'.$dane['HostName'].'</span></td>
                <td class="trow2"> <span class="smalltext">'.$mc['ip'].':'.$mc['port'].'</span></td>
                <td class="trow2"> <span class="smalltext">'.$dane['Players'].'/'.$dane['MaxPlayers'].'</span></td>
				<td class="trow2"> <span class="smalltext">'.$dane['Map'].'</span></td>
                <td class="trow2"> <span class="smalltext">'.$mc['rodzaj'].'</span></td>
                <td class="trow2"> <span class="smalltext">'.$status.'</span></td>
                <td class="trow2"> <span class="smalltext"><a title="'.$dane['Software'].'">'.$dane['Version'].'</a><span></td>
            </tr>';
         
   $serwery_l = $db->num_rows($query);      
   $slot_l = $slot_l + $dane['MaxPlayers'];
	$gracze_l = $gracze_l + $dane['Players'];
    }
    $mcboard .= '
    <tr><td class="trow1" align="center" colspan="7"> Na naszych '.$serwery_l.' serwerach, które mają w sumie '.$slot_l.' slotów jest '.$gracze_l.' graczy online.  </td></tr>
        </table><br />';
}
?>
