<?php

if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}
 
$page->add_breadcrumb_item("MCBoard", "index.php?module=config/mcboard"); 
 
if($mybb->input['action'] == "add" || $mybb->input['action'] == "edit" || !$mybb->input['action'])
{
    $sub_tabs['mcboard'] = array(
        'title' => "MCBoard",
        'link' => "index.php?module=config/mcboard",
        'description' => "Lista dodanych serwerów."
    );
    $sub_tabs['addmcboard'] = array( 
        'title' => "Dodaj Serwer", 
        'link' => "index.php?module=config/mcboard&amp;action=add", 
        'description' => "Dodaj serwer!" 
    );
	
	$sub_tabs['editmcboard'] = array( 
        'title' => "Edytuj serwer", 
        'link' => "index.php?module=config/mcboard&amp;action=edit", 
        'description' => "Edytuj serwer!" 
    );
}
 
#/\ dodawanie nowych subsub menu.
 
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
		
		flash_message("Serwer został dodany!", 'success');
		admin_redirect("index.php?module=config/mcboard");
	}
	
   
    $page->add_breadcrumb_item("MCBoard");
    $page->output_header("MCBoard", "Dodaj Serwer"); 
   
    $page->output_nav_tabs($sub_tabs, 'addmcboard');
    
    	$query = $db->simple_select("mcboard"); 
	$admin_options = $db->fetch_array($query);
	
	$form = new Form("index.php?module=config/mcboard&amp;action=add", "post", "add"); 

		if($errors) 
	{
		$page->output_inline_error($errors);
	}

	$form_container = new FormContainer("Dane");
	$form_container->output_row("IP:", "Podaj IP serwera.", $form->generate_text_box('ip'));
		$form_container->output_row("Port:", "Podaj Port serwera.", $form->generate_text_box('port'));
	$select_list = array("Premium", "NonPremium");
	$form_container->output_row("Rodzaj:", "Wybierz rodzaj serwera.", $form->generate_select_box('rodzaj', $select_list));
	$form_container->end();
	
	$buttons[] = $form->generate_submit_button("Dodaj serwer");
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
		flash_message("Serwer nie został pomyślnie usunięty.", 'error');
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

		flash_message("Serwer został usunięty pomyślnie.", 'success');
		admin_redirect("index.php?module=config/mcboard");
	}
	else
	{
		$page->output_confirm_action("index.php?module=config/mcboard&amp;action=delete&amp;id={$mc['id']}", "Serwer został usunięty pomyślnie.");
	}
}

if($mybb->input['action'] == "edit")
{
	$plugins->run_hooks("admin_mcboard_edit");
	
	$query = $db->simple_select("mcboard", "*", "id='".intval($mybb->input['id'])."'");
	$mc = $db->fetch_array($query);
	

	if(!$mc['id'])
	{
		flash_message("Proszę wybrać serwer do edycji.", 'error');
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

			flash_message("Serwer został wyedytowany pomyślnie.", 'success');
			admin_redirect("index.php?module=config/mcboard");
	
	}
	
	$page->add_breadcrumb_item("Edycja");
	$page->output_header("MCBoard - Edycja");
	
	$page->output_nav_tabs($sub_tabs, 'editmcboard');
	$form = new Form("index.php?module=config/mcboard&amp;action=edit", "post");

	$form_container = new FormContainer("Edycja serwera");
	$form_container->output_row("IP:", "Podaj IP serwera.", $form->generate_text_box('ip', htmlspecialchars_uni($mc['ip'])));
		$form_container->output_row("Port:", "Podaj Port serwera.", $form->generate_text_box('port', htmlspecialchars_uni($mc['port'])));
	$select_list = array("Premium", "NonPremium");
	$form_container->output_row("Rodzaj:", "Wybierz rodzaj serwera.", $form->generate_select_box('rodzaj', $select_list, htmlspecialchars_uni($mc['rodzaj'])));
	$form_container->end();
	
	echo $form->generate_hidden_field("id", $mc['id']);

	$buttons[] = $form->generate_submit_button("Zapisz");

	$form->output_submit_wrapper($buttons);
	$form->end();

	$page->output_footer();
}

if(!$mybb->input['action'])
{
   
    $page->add_breadcrumb_item("MCBoard");
    $page->output_header("MCBoard", "Dodaj serwer");
   
    $page->output_nav_tabs($sub_tabs, 'mcboard');
    
    $table = new Table;
    $table->construct_header("Nazwa");
    $table->construct_header("IP");
    $table->construct_header("Sloty");
	$table->construct_header("Mapa");
    $table->construct_header("Rodzaj");
    $table->construct_header("Status");
	$table->construct_header("Opcje");
   
   $query = $db->simple_select('mcboard', '*');
   
			while($mc = $db->fetch_array($query)) {
        if(!$mc['rodzaj'] == 2) {
            $mc['rodzaj'] = 'Premium';
        } elseif($mc['rodzaj'] < 2) {
            $mc['rodzaj'] = 'NonPremium';
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
		$status = 'Offline';
	} 
	else {
		$status = 'Online';
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
		$table->construct_cell("<a href=\"index.php?module=config/mcboard&amp;action=delete&amp;id={$mc['id']}&amp;my_post_key={$mybb->post_code}\" onclick=\"return AdminCP.deleteConfirmation(this, 'Czy na pewno chcesz usunąć ten serwer?')\">Usuń</a> | <a href=\"index.php?module=config/mcboard&amp;action=edit&amp;id={$mc['id']}\">Edytuj</a>");
        $table->construct_row();
        
 }
		
}
   
	$table->output("Serwery");
 
    $page->output_footer();
?>
