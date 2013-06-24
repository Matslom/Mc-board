<?php

/* 
 * Plugin MCBoard for MyBB by Matslom [matslom.pl]; Copyright (C) 2012-2013
 * Github https://github.com/Matslom/Mc-board.git
 * License GNU GENERAL PUBLIC LICENSE -> http://www.gnu.org/licenses/gpl-3.0.txt
 * Do not remove this information
 */

if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}
 
$page->add_breadcrumb_item("MCBoard", "index.php?module=config/mcboard"); 
$lang->load("mcboard");
 
if($mybb->input['action'] == "add" || $mybb->input['action'] == "edit" || !$mybb->input['action'])
{
    $sub_tabs['mcboard'] = array(
        'title' => $lang->title_list,
        'link' => "index.php?module=config/mcboard",
        'description' => $lang->desc_list,
    );
    $sub_tabs['addmcboard'] = array( 
        'title' => $lang->title_add, 
        'link' => "index.php?module=config/mcboard&amp;action=add", 
        'description' => $lang->desc_add, 
    );
	
	$sub_tabs['editmcboard'] = array( 
        'title' => $lang->title_edit, 
        'link' => "index.php?module=config/mcboard&amp;action=edit", 
        'description' => $lang->desc_edit, 
    );
}
 
if($mybb->input['action'] == "add")
{
	if($mybb->request_method == "post")
	{
		
		$sqlarray = array(
			"ip" => $db->escape_string($mybb->input['ip']),
			"port" => $db->escape_string($mybb->input['port']),
			"rodzaj" => $db->escape_string($mybb->input['rodzaj']),
		); 

		$db->insert_query("mcboard", $sqlarray);
		
		flash_message($lang->info_add_success , 'success');
		admin_redirect("index.php?module=config/mcboard");
	}
	
   
    $page->add_breadcrumb_item("MCBoard");
    $page->output_header("MCBoard", $lang->title_add); 
   
    $page->output_nav_tabs($sub_tabs, 'addmcboard');
    
    	$query = $db->simple_select("mcboard"); 
	$admin_options = $db->fetch_array($query);
	
	$form = new Form("index.php?module=config/mcboard&amp;action=add", "post", "add"); 

		if($errors) 
	{
		$page->output_inline_error($errors);
	}

	$form_container = new FormContainer($lang->form_name);
	$form_container->output_row($lang->ip .":", $lang->desc_ip, $form->generate_text_box('ip'));
	$form_container->output_row($lang->port .":", $lang->desc_port, $form->generate_text_box('port'));
	$select_list = array($lang->premium , $lang->nonpremium);
	$form_container->output_row($lang->type .":", $lang->desc_type, $form->generate_select_box('rodzaj', $select_list));
	$form_container->end();
	
	$buttons[] = $form->generate_submit_button($lang->add_submit);
	$form->output_submit_wrapper($buttons);
	
	$form->end();
 
    $page->output_footer();
}

if($mybb->input['action'] == "delete")
{
	$plugins->run_hooks("admin_config_mcboard_delete");
	
	$query = $db->simple_select("mcboard", "*", "id='".intval($mybb->input['id'])."'");
	$mc = $db->fetch_array($query);
	
	if(!$mc['id'])
	{
		flash_message($lang->info_del_error, 'error');
		admin_redirect("index.php?module=config/mcboard");
	}

	if($mybb->input['no'])
	{
		admin_redirect("index.php?module=config/mcboard");
	}

	if($mybb->request_method == "post")
	{
		$db->delete_query("mcboard", "id='{$mc['id']}'");
		
		$plugins->run_hooks("admin_config_mcboard_delete_commit");

		flash_message($lang->info_del_success, 'success');
		admin_redirect("index.php?module=config/mcboard");
	}
	else
	{
		$page->output_confirm_action("index.php?module=config/mcboard&amp;action=delete&amp;id={$mc['id']}", $lang->info_del_success);
	}
}

if($mybb->input['action'] == "edit")
{
	$plugins->run_hooks("admin_mcboard_edit");
	
	$query = $db->simple_select("mcboard", "*", "id='".intval($mybb->input['id'])."'");
	$mc = $db->fetch_array($query);
	

	if(!$mc['id'])
	{
		flash_message($lang->info_no_server, 'error');
		admin_redirect("index.php?module=config/mcboard");
	}

	if($mybb->request_method == "post")
	{

			$mc = array(
			"ip" => $db->escape_string($mybb->input['ip']),
			"port" => $db->escape_string($mybb->input['port']),
			"rodzaj" => $db->escape_string($mybb->input['rodzaj']),
			);
			
			$db->update_query("mcboard", $mc, "id = '".intval($mybb->input['id'])."'");
			
			$plugins->run_hooks("admin_config_mcboard_edit_commit");

			flash_message($lang->info_edit_success, 'success');
			admin_redirect("index.php?module=config/mcboard");
	
	}
	
	$page->add_breadcrumb_item($slang->title_edit);
	$page->output_header("MCBoard - ".$slang->title_edit);
	
	$page->output_nav_tabs($sub_tabs, 'editmcboard');
	$form = new Form("index.php?module=config/mcboard&amp;action=edit", "post");

	$form_container = new FormContainer($lang->edit_form_name);
	$form_container->output_row($lang->ip .":", $lang->desc_ip, $form->generate_text_box('ip', htmlspecialchars_uni($mc['ip'])));
	$form_container->output_row($lang->port .":", $lang->desc_port, $form->generate_text_box('port', htmlspecialchars_uni($mc['port'])));
	$select_list = array($lang->premium , $lang->nonpremium);
	$form_container->output_row($lang->type .":", $lang->desc_type, $form->generate_select_box('rodzaj', $select_list, htmlspecialchars_uni($mc['rodzaj'])));
	$form_container->end();
	
	echo $form->generate_hidden_field("id", $mc['id']);

	$buttons[] = $form->generate_submit_button($lang->edit_submit);

	$form->output_submit_wrapper($buttons);
	$form->end();

	$page->output_footer();
}

if(!$mybb->input['action'])
{
   
    $page->add_breadcrumb_item("MCBoard");
    $page->output_header("MCBoard", $lang->title_add);
   
    $page->output_nav_tabs($sub_tabs, 'mcboard');
    
    $table = new Table;
    $table->construct_header($lang->name);
    $table->construct_header($lang->ip);
    $table->construct_header($lang->slott);
	$table->construct_header($lang->map);
    $table->construct_header($lang->type);
    $table->construct_header($lang->status);
	$table->construct_header($lang->options);
   
   $query = $db->simple_select('mcboard', '*');
   
			while($mc = $db->fetch_array($query)) {
        if(!$mc['rodzaj'] == 2) {
            $mc['rodzaj'] = $lang->premium;
        } elseif($mc['rodzaj'] < 2) {
            $mc['rodzaj'] = $lang->nonpremium;
       }
        $IP = $mc['ip'];
        $PORT = $mc['port']; 
		
	require_once MYBB_ROOT."inc/plugins/minequery/MinecraftQuery.class.php";
	try{
		$Query = new MinecraftQuery();
 		$Query->Connect($IP, $PORT, 1);
		
    }
    catch( MinecraftQueryException $e ){
        $error = $e->getMessage( );
    }

        $dane = $Query->GetInfo();
	
	if($dane['MaxPlayers'] == NULL){
		$status = $lang->offline;
	} 
	else {
		$status = $lang->online;
	}
	
	if ($dane['Players'] === NULL) {
		$dane['Players'] = '-';
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
							
        $table->construct_cell($dane['HostName']);
        $table->construct_cell($mc['ip'].':'.$mc['port']);
        $table->construct_cell($dane['Players'].'/'.$dane['MaxPlayers']);
		$table->construct_cell($dane['Map']);
		$table->construct_cell($mc['rodzaj']);
        $table->construct_cell($status);
		$table->construct_cell("<a href=\"index.php?module=config/mcboard&amp;action=delete&amp;id={$mc['id']}&amp;my_post_key={$mybb->post_code}\" onclick=\"return AdminCP.deleteConfirmation(this, ".$lang->delete_confirm .")\">". $lang->delete ."</a> | <a href=\"index.php?module=config/mcboard&amp;action=edit&amp;id={$mc['id']}\">". $lang->edit ."</a>");
        $table->construct_row();
        
 }
		
}
   
	$table->output("Serwery");
 
    $page->output_footer();
?>
